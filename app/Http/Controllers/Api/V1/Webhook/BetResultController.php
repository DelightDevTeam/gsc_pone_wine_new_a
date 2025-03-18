<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\ResultWebhookRequest;
use App\Models\Admin\GameList;
use App\Models\User;
use App\Models\Webhook\Bet;
use App\Models\Webhook\Result;
use App\Traits\UseWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BetResultController extends Controller
{
    use UseWebhook;

    public function handleResult(ResultWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            foreach ($transactions as $transaction) {
                $player = $this->validatePlayer($transaction['PlayerId']);
                if (! $player) {
                    return $this->buildErrorResponse(StatusCode::InvalidPlayerPassword, 0);
                }

                if (! $this->validateRoundId($transaction['RoundId'])) {
                    return $this->buildErrorResponse(StatusCode::BetTransactionNotFound, 0);
                }

                $lockKey = "wallet:lock:{$player->id}";
                if (! $this->acquireLock($lockKey)) {
                    return $this->buildErrorResponse(StatusCode::BetTransactionNotFound, $player->wallet->balanceFloat);
                }

                try {
                    if (! $this->isValidSignature($transaction)) {
                        return $this->buildErrorResponse(StatusCode::InvalidSignature, $player->wallet->balanceFloat);
                    }

                    if ($this->isDuplicateResult($transaction)) {
                        return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $player->wallet->balanceFloat);
                    }

                    if ($transaction['WinAmount'] > 0) {
                        $this->processTransfer(
                            User::adminUser(),
                            $player,
                            TransactionName::Payout,
                            $transaction['WinAmount']
                        );
                    }

                    $player->wallet->refreshBalance();
                    $this->logGameAndCreateResult($transaction, $player);
                } finally {
                    $this->releaseLock($lockKey);
                }
            }

            DB::commit();

            return $this->buildSuccessResponse($player->wallet->balanceFloat ?? 0);
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

    private function validatePlayer(string $playerId): ?User
    {
        return User::where('user_name', $playerId)->first();
    }

    private function validateRoundId(string $roundId): bool
    {
        return Bet::where('round_id', $roundId)->exists();
    }

    private function acquireLock(string $key): bool
    {
        return Redis::set($key, true, 'EX', 10, 'NX') !== null;
    }

    private function releaseLock(string $key): void
    {
        Redis::del($key);
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

    private function isValidSignature(array $transaction): bool
    {
        $generatedSignature = $this->generateSignature($transaction);
        Log::info('Generated result signature', ['GeneratedSignature' => $generatedSignature]);

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

            Log::info('Game result logged successfully', ['PlayerId' => $transaction['PlayerId'], 'ResultId' => $transaction['ResultId']]);
        } catch (\Exception $e) {
            Log::error('Failed to log game result', [
                'PlayerId' => $transaction['PlayerId'],
                'Error' => $e->getMessage(),
                'ResultId' => $transaction['ResultId'],
            ]);
        }
    }
}
