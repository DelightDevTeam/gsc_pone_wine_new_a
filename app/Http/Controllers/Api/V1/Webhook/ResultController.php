<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\ResultWebhookRequest;
use App\Models\Admin\GameList;
use App\Models\User;
use App\Models\Webhook\Result;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    use UseWebhook;

    public function handleResult(ResultWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            Log::info('Starting handleResult method for multiple transactions');

            foreach ($transactions as $transaction) {
                $player = User::where('user_name', $transaction['PlayerId'])->first();
                if (! $player) {
                    Log::warning('Invalid player detected', [
                        'PlayerId' => $transaction['PlayerId'],
                    ]);

                    return $this->buildErrorResponse(StatusCode::InvalidPlayerPassword, 0);
                }

                // Validate signature
                $signature = $this->generateSignature($transaction);
                //$signature = $this->generateSignature($transaction);
                Log::info('Result Signature', ['GeneratedResultSignature' => $signature]);
                if ($signature !== $transaction['Signature']) {
                    Log::warning('Signature validation failed for transaction', [
                        'transaction' => $transaction,
                        'generated_signature' => $signature,
                    ]);

                    return $this->buildErrorResponse(StatusCode::InvalidSignature, $player->wallet->balanceFloat);
                }

                // Check for duplicate ResultId
                $existingTransaction = Result::where('result_id', $transaction['ResultId'])->first();
                if ($existingTransaction) {
                    Log::warning('Duplicate ResultId detected', ['ResultId' => $transaction['ResultId']]);
                    $balance = $player->wallet->balanceFloat;

                    return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $balance);
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

                Log::info('Transaction WinAmount successfully', ['WinAmount' => $transaction['WinAmount']]);

                // Refresh player's balance after processing the transaction
                $player->wallet->refreshBalance();
                $newBalance = $player->wallet->balanceFloat;

                // Get game information
                $game = GameList::where('game_code', $transaction['GameCode'])->first();
                $game_name = $game ? $game->game_name : null;
                $provider_name = $game ? $game->game_provide_name : null;

                // Create result record
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

                Log::info('Result transaction processed successfully', ['ResultId' => $transaction['ResultId']]);
            }

            DB::commit();
            // Log::info('All result transactions committed successfully');

            // Return the latest balance of the last processed player
            return $this->buildSuccessResponse($newBalance);
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
            'Balance' => round($balance, 4),
        ]);
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
}
