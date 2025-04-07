<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\ResultWebhookRequest;
use App\Models\Admin\GameList;
use App\Models\User;
use App\Models\Webhook\Result;
use App\Services\WalletService;
use App\Traits\UseWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class NewBetResultController extends Controller
{
    use UseWebhook;

    public function handleResult(ResultWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            // Log::info('Starting handleResult method for multiple transactions');

            foreach ($transactions as $transaction) {
                $player = User::where('user_name', $transaction['PlayerId'])->first();
                if (! $player) {
                    Log::warning('Invalid player detected', ['PlayerId' => $transaction['PlayerId']]);

                    return $this->buildErrorResponse(StatusCode::InvalidPlayerPassword, 0);
                }

                // Acquire a Redis lock for the player's wallet
                $lockKey = "wallet:lock:{$player->id}";
                $lock = Redis::set($lockKey, true, 'EX', 10, 'NX'); // 10-second lock

                if (! $lock) {
                    return $this->buildErrorResponse(StatusCode::BetTransactionNotFound, $player->wallet->balanceFloat);
                }

                try {

                    // Validate signature
                    if (! $this->isValidSignature($transaction)) {
                        Redis::del($lockKey); // Release lock

                        return $this->buildErrorResponse(StatusCode::InvalidSignature, $player->wallet->balanceFloat);
                    }

                    // Prevent duplicate ResultId (concern with duplicate transaction)
                    if ($this->isDuplicateResult($transaction)) {
                        Redis::del($lockKey); // Release lock

                        return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $player->wallet->balanceFloat);
                    }

                    // Process payout if WinAmount > 0
                    if ($transaction['WinAmount'] > 0) {
                        $this->processTransfer(
                            User::adminUser(),
                            $player,
                            TransactionName::Payout,
                            $transaction['WinAmount']
                        );
                    }

                    // Refresh balance
                    $player->wallet->refreshBalance();
                    $newBalance = $player->wallet->balanceFloat;

                    // Log game info and create result record
                    $this->logGameAndCreateResult($transaction, $player);

                } finally {
                    // Release the Redis lock for the wallet
                    Redis::del($lockKey);
                }
            }

            DB::commit();

            return $this->buildSuccessResponse($newBalance ?? 0);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle Result transactions', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Failed to handle Result transactions'], 500);
        }
    }

    private function buildSuccessResponse(float $newBalance): JsonResponse
    {
        return response()->json([
            'Status' => StatusCode::OK->value,
            'Description' => 'Success',
            'ResponseDateTime' => now()->format('Y-m-d H:i:s'),
            'Balance' => round($newBalance, 4),
        ]);
    }

    private function buildErrorResponse(StatusCode $statusCode, float $balance = 0): JsonResponse
    {
        return response()->json([
            'Status' => $statusCode->value,
            'Description' => $statusCode->name,
            'ResponseDateTime' => now()->format('Y-m-d H:i:s'),
            'Balance' => round($balance, 4),
        ]);
    }
    //     private function buildErrorResponse(StatusCode $statusCode, float $balance = 0): JsonResponse
    // {
    //     $httpStatus = $this->mapToHttpStatus($statusCode);

    //     return response()->json([
    //         'Status' => $statusCode->value,           // Custom status code in the body
    //         'Description' => $statusCode->name,      // Custom status description
    //         'Balance' => round($balance, 4),         // Player's balance
    //     ], $httpStatus);                            // Valid HTTP status code in the header
    // }

    private function mapToHttpStatus(StatusCode $statusCode): int
    {
        return match ($statusCode) {
            StatusCode::DuplicateTransaction,
            StatusCode::InvalidSignature,
            StatusCode::BetTransactionNotFound => 409, // Conflict
            StatusCode::InternalServerError => 500,    // Internal Server Error
            StatusCode::BadRequest => 400,            // Bad Request
            default => 400,                           // Default to Bad Request
        };
    }

    private function isValidSignature(array $transaction): bool
    {
        $generatedSignature = $this->generateSignature($transaction);
        // log remove
        //Log::info('Generated result signature', ['GeneratedSignature' => $generatedSignature]);

        if ($generatedSignature !== $transaction['Signature']) {
            Log::warning('Signature validation failed for transaction', [
                'transaction' => $transaction,
                'generated_signature' => $generatedSignature,
            ]);

            return false;
        }

        return true;
    }

    private function generateSignature(array $transaction): string
    {
        $method = 'Result';

        // signature is correct but not launch game (wait provider respond)

        return md5(
            $method.
            $transaction['RoundId'].
            $transaction['ResultId'].
            $transaction['RequestDateTime'].
            $transaction['OperatorId'].
            config('game.api.secret_key').
            $transaction['PlayerId']
        );
    }

    private function isDuplicateResult(array $transaction): bool
    {
        $existingTransaction = Result::where('result_id', $transaction['ResultId'])->first();
        if ($existingTransaction) {
            Log::warning('Duplicate ResultId detected', ['ResultId' => $transaction['ResultId']]);

            return true;
        }

        return false;
    }

    private function logGameAndCreateResult($transaction, $player)
    {
        // Retrieve game information based on the game code
        $game = GameList::where('game_code', $transaction['GameCode'])->first();
        $game_name = $game ? $game->game_name : null;
        $provider_name = $game ? $game->game_provide_name : null;

        // Create a result record in the database
        try {
            Result::create([
                'user_id' => $player->id,
                'player_name' => $player->name,
                'game_provide_name' => $provider_name,
                'game_name' => $game_name,
                'operator_id' => $transaction['OperatorId'],
                'request_date_time' => $transaction['RequestDateTime'],
                'signature' => $transaction['Signature'],
                'player_id' => $transaction['PlayerId'],
                'currency' => $transaction['Currency'],
                'round_id' => $transaction['RoundId'],
                'bet_ids' => $transaction['BetIds'],
                'result_id' => $transaction['ResultId'],
                'game_code' => $transaction['GameCode'],
                'total_bet_amount' => $transaction['TotalBetAmount'],
                'win_amount' => $transaction['WinAmount'],
                'net_win' => $transaction['NetWin'],
                'tran_date_time' => $transaction['TranDateTime'],
            ]);

            // Log::info('Game result logged successfully', ['PlayerId' => $transaction['PlayerId'], 'ResultId' => $transaction['ResultId']]);
        } catch (\Exception $e) {
            Log::error('Failed to log game result', [
                'PlayerId' => $transaction['PlayerId'],
                'Error' => $e->getMessage(),
                'ResultId' => $transaction['ResultId'],
            ]);
        }
    }
}
