<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\BetNResultWebhookRequest;
use App\Models\User;
use App\Models\Webhook\BetNResult;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BetNResultController extends Controller
{
    use UseWebhook;

    public function handleBetNResult(BetNResultWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            // Log::info('Starting handleBetNResult method for multiple transactions');

            foreach ($transactions as $transaction) {
                // Get the player
                $player = User::where('user_name', $transaction['PlayerId'])->first();
                if (! $player) {
                    Log::warning('Invalid player detected', [
                        'PlayerId' => $transaction['PlayerId'],
                    ]);

                    return PlaceBetWebhookService::buildResponse(
                        StatusCode::InvalidPlayerPassword,
                        0,
                        0
                    );
                }

                // Validate transaction signature
                $signature = $this->generateSignature($transaction);
                if ($signature !== $transaction['Signature']) {
                    Log::warning('Signature validation failed', [
                        'transaction' => $transaction,
                        'generated_signature' => $signature,
                    ]);

                    return $this->buildErrorResponse(StatusCode::InvalidSignature);
                }

                // Check for duplicate transaction
                $existingTransaction = BetNResult::where('tran_id', $transaction['TranId'])->first();
                if ($existingTransaction) {
                    Log::warning('Duplicate TranId detected', [
                        'TranId' => $transaction['TranId'],
                    ]);
                    $Balance = $request->getMember()->balanceFloat;

                    return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $Balance);
                }

                $PlayerBalance = $request->getMember()->balanceFloat;

                // Check for sufficient balance
                if ($transaction['BetAmount'] > $PlayerBalance) {
                    Log::warning('Insufficient balance detected', [
                        'BetAmount' => $transaction['BetAmount'],
                        'balance' => $PlayerBalance,
                    ]);

                    return $this->buildErrorResponse(StatusCode::InsufficientBalance, $PlayerBalance);
                }

                // Process the bet
                // $this->processTransfer(
                //     $player,
                //     User::adminUser(), // Assuming admin user as the receiving party
                //     TransactionName::Stake,
                //     $transaction['BetAmount']
                // );

                // $request->getMember()->wallet->refreshBalance();

                // $NewBalance = $request->getMember()->balanceFloat;
                // Calculate NetWin based on the WinAmount and BetAmount
                $netWin = $transaction['WinAmount'] - $transaction['BetAmount'];

                // Adjust the balance based on NetWin
                if ($netWin > 0) {
                    // Log NetWin positive adjustment
                    // Log::info('NetWin is positive, increasing balance', [
                    //     'PlayerID' => $transaction['PlayerId'],
                    //     'NetWin' => $netWin,
                    // ]);
                    // Increase balance by NetWin
                    $this->processTransfer(User::adminUser(), $player, TransactionName::Win, $netWin);
                } elseif ($netWin < 0) {
                    // Log NetWin negative adjustment
                    // Log::info('NetWin is negative, decreasing balance', [
                    //     'PlayerID' => $transaction['PlayerId'],
                    //     'NetWin' => $netWin,
                    //     'AbsoluteNetWin' => abs($netWin),
                    // ]);
                    // Decrease balance by the absolute value of NetWin
                    $this->processTransfer($player, User::adminUser(), TransactionName::Loss, abs($netWin));
                }
                // else {
                //     // Log case where NetWin is zero
                //     Log::info('NetWin is zero, no balance adjustment required', [
                //         'PlayerID' => $transaction['PlayerId'],
                //     ]);
                // }

                // Refresh and get the updated balance
                $request->getMember()->wallet->refreshBalance();
                $newBalance = $request->getMember()->balanceFloat;

                // Create the transaction record
                BetNResult::create([
                    'user_id' => $player->id,
                    'operator_id' => $transaction['OperatorId'],
                    'request_date_time' => $transaction['RequestDateTime'],
                    'signature' => $transaction['Signature'],
                    'player_id' => $transaction['PlayerId'],
                    'currency' => $transaction['Currency'],
                    'tran_id' => $transaction['TranId'],
                    'game_code' => $transaction['GameCode'],
                    'bet_amount' => $transaction['BetAmount'],
                    'win_amount' => $transaction['WinAmount'],
                    'net_win' => $transaction['WinAmount'] - $transaction['BetAmount'],
                    'tran_date_time' => Carbon::parse($transaction['TranDateTime'])->format('Y-m-d H:i:s'),
                    'auth_token' => $transaction['AuthToken'] ?? 'default_password',
                    'status' => 'processed',

                ]);

                //Log::info('Transaction processed successfully', ['TranId' => $transaction['TranId']]);
            }

            DB::commit();
            // Log::info('All transactions committed successfully');

            // Build a successful response with the final balance of the last player
            return $this->buildSuccessResponse($newBalance);
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
            'Balance' => round($balance, 4),
        ]);
    }

    private function generateSignature(array $transaction): string
    {
        $method = 'BetNResult';
        $tranId = $transaction['TranId'];
        $requestTime = $transaction['RequestDateTime'];
        $operatorCode = $transaction['OperatorId'];
        $secretKey = config('game.api.secret_key'); // Fetch secret key from config
        $playerId = $transaction['PlayerId'];

        return md5($method.$tranId.$requestTime.$operatorCode.$secretKey.$playerId);
    }
}
