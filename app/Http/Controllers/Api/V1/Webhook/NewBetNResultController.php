<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\BetNResultWebhookRequest;
use App\Models\Admin\GameList;
use App\Models\User;
use App\Models\Webhook\BetNResult;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewBetNResultController extends Controller
{
    use UseWebhook;

    public function handleBetNResult(BetNResultWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            foreach ($transactions as $transaction) {
                // Get the player
                $player = User::where('user_name', $transaction['PlayerId'])->first();
                if (! $player) {
                    Log::warning('Invalid player detected', ['PlayerId' => $transaction['PlayerId']]);

                    return PlaceBetWebhookService::buildResponse(StatusCode::InvalidPlayerPassword, 0, 0);
                }

                // Validate transaction signature
                $signature = $this->generateSignature($transaction);
                if ($signature !== $transaction['Signature']) {
                    Log::warning('Signature validation failed', ['transaction' => $transaction, 'generated_signature' => $signature]);

                    return $this->buildErrorResponse(StatusCode::InvalidSignature);
                }

                // Check for duplicate transaction
                $existingTransaction = BetNResult::where('tran_id', $transaction['TranId'])->first();
                if ($existingTransaction) {
                    Log::warning('Duplicate TranId detected', ['TranId' => $transaction['TranId']]);

                    return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $player->wallet->balanceFloat);
                }

                // Capture BEFORE balance
                $beforeBalance = $player->wallet->balanceFloat;

                // Check for sufficient balance
                if ($transaction['BetAmount'] > $beforeBalance) {
                    Log::warning('Insufficient balance detected', [
                        'BetAmount' => $transaction['BetAmount'],
                        'balance' => $beforeBalance,
                    ]);

                    return $this->buildErrorResponse(StatusCode::InsufficientBalance, $beforeBalance);
                }

                // Calculate NetWin
                $netWin = $transaction['WinAmount'] - $transaction['BetAmount'];

                // Adjust the balance based on NetWin
                if ($netWin > 0) {
                    // Increase balance by NetWin
                    $this->processTransfer(User::adminUser(), $player, TransactionName::Win, $netWin);
                } elseif ($netWin < 0) {
                    // Decrease balance by the absolute value of NetWin
                    $this->processTransfer($player, User::adminUser(), TransactionName::Loss, abs($netWin));
                }

                // Refresh and capture AFTER balance
                $player->wallet->refreshBalance();
                $afterBalance = $player->wallet->balanceFloat;

                // Retrieve game information based on the game code
                $game = GameList::where('game_code', $transaction['GameCode'])->first();
                // $game_name = $game ? $game->game_name : null;
                // $provider_name = $game ? $game->game_provide_name : null;
                $game_name = $game ? $game->game_name : 'Unknown Game';
                $provider_name = $game ? $game->game_provide_name : 'Unknown Provider';

                Log::info('Transaction processed successfully', [
                    'TranId' => $transaction['TranId'],
                    'Game Name' => $game_name,
                    'ProviderName' => $provider_name,
                ]);
                // Create the transaction record
                // BetNResult::create([
                //     'user_id' => $player->id,
                //     'operator_id' => $transaction['OperatorId'],
                //     'request_date_time' => $transaction['RequestDateTime'],
                //     'signature' => $transaction['Signature'],
                //     'player_id' => $transaction['PlayerId'],
                //     'currency' => $transaction['Currency'],
                //     'tran_id' => $transaction['TranId'],
                //     'game_code' => $transaction['GameCode'],
                //     'game_name' => $game_name,
                //     'bet_amount' => $transaction['BetAmount'],
                //     'win_amount' => $transaction['WinAmount'],
                //     'net_win' => $netWin,
                //     'tran_date_time' => Carbon::parse($transaction['TranDateTime'])->format('Y-m-d H:i:s'),
                //     'provider_code' => $provider_name,
                //     'auth_token' => $transaction['AuthToken'] ?? 'default_password',
                //     'status' => 'processed',
                //     'old_balance' => $beforeBalance, // Store BEFORE balance
                //     'new_balance' => $afterBalance,  // Store AFTER balance
                // ]);
                DB::table('bet_n_results')->insert([
                    'user_id' => $player->id,
                    'operator_id' => $transaction['OperatorId'],
                    'request_date_time' => $transaction['RequestDateTime'],
                    'signature' => $transaction['Signature'],
                    'player_id' => $transaction['PlayerId'],
                    'currency' => $transaction['Currency'],
                    'tran_id' => $transaction['TranId'],
                    'game_code' => $transaction['GameCode'],
                    'game_name' => $game_name,
                    'bet_amount' => $transaction['BetAmount'],
                    'win_amount' => $transaction['WinAmount'],
                    'net_win' => $netWin,
                    'tran_date_time' => Carbon::parse($transaction['TranDateTime'])->format('Y-m-d H:i:s'),
                    'provider_code' => $provider_name,
                    'auth_token' => $transaction['AuthToken'] ?? 'default_password',
                    'status' => 'processed',
                    'old_balance' => $beforeBalance,
                    'new_balance' => $afterBalance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Transaction processed successfully', [
                    'TranId' => $transaction['TranId'],
                    'Before Balance' => $beforeBalance,
                    'After Balance' => $afterBalance,
                ]);
            }

            DB::commit();

            return $this->buildSuccessResponse($afterBalance);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle BetNResult', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Failed to handle BetNResult'], 500);
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

    private function generateSignature(array $transaction): string
    {
        return md5(
            'BetNResult'.
            $transaction['TranId'].
            $transaction['RequestDateTime'].
            $transaction['OperatorId'].
            config('game.api.secret_key').
            $transaction['PlayerId']
        );
    }
}
