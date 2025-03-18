<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\CancelBetNResultRequest;
use App\Models\User;
use App\Models\Webhook\BetNResult;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelBetNResultController extends Controller
{
    use UseWebhook;

    public function handleCancelBetNResult(CancelBetNResultRequest $request): JsonResponse
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
                // if ($signature !== $transaction['Signature']) {
                //     Log::warning('Signature validation failed', [
                //         'transaction' => $transaction,
                //         'generated_signature' => $signature,
                //     ]);

                //     return $this->buildErrorResponse(StatusCode::InvalidSignature);
                // }

                // Check for duplicate transaction
                $existingTransaction = BetNResult::where('tran_id', $transaction['TranId'])->first();
                // if ($existingTransaction) {
                //     Log::warning('Duplicate TranId detected', [
                //         'TranId' => $transaction['TranId'],
                //     ]);
                //     $Balance = $request->getMember()->balanceFloat;

                //     return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $Balance);
                // }

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
                $this->processTransfer(
                    User::adminUser(), // Assuming admin user as the receiving party
                    $player,
                    TransactionName::Refund,
                    $transaction['BetAmount']
                );

                $request->getMember()->wallet->refreshBalance();

                $NewBalance = $request->getMember()->balanceFloat;

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
                    'tran_date_time' => Carbon::parse($transaction['TranDateTime'])->format('Y-m-d H:i:s'),
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                Log::info('Transaction Refund processed successfully', ['TranId' => $transaction['TranId']]);
            }

            DB::commit();
            // Log::info('All transactions Refund committed successfully');

            // Build a successful response with the final balance of the last player
            return $this->buildSuccessResponse($NewBalance);
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
        $method = 'CancelBetNResult';
        $tranId = $transaction['TranId'];
        $requestTime = $transaction['RequestDateTime'];
        $operatorCode = $transaction['OperatorId'];
        $secretKey = config('game.api.secret_key'); // Fetch secret key from config
        $playerId = $transaction['PlayerId'];

        return md5($method.$tranId.$requestTime.$operatorCode.$secretKey.$playerId);
    }
}
