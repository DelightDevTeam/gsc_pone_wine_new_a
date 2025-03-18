<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\AdjustmentWebhookRequest;
use App\Models\User;
use App\Models\Webhook\Adjustment;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentController extends Controller
{
    use UseWebhook;

    public function handleAdjustment(AdjustmentWebhookRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            // Log::info('Starting handleAdjustment method for multiple transactions');

            foreach ($transactions as $transaction) {
                // Retrieve the player
                $player = User::where('user_name', $transaction['PlayerId'])->first();
                if (! $player) {
                    Log::warning('Invalid player detected', [
                        'PlayerId' => $transaction['PlayerId'],
                    ]);

                    return $this->buildErrorResponse(StatusCode::InvalidPlayerPassword, 0);
                }

                // Validate signature
                $signature = $this->generateSignature($transaction);
                // Log::info('Adjustment Signature', ['GeneratedAdjustmentSignature' => $signature]);

                if ($signature !== $transaction['Signature']) {
                    Log::warning('Signature validation failed', [
                        'transaction' => $transaction,
                        'generated_signature' => $signature,
                    ]);

                    return $this->buildErrorResponse(StatusCode::InvalidSignature, 0);
                }

                // Check for duplicate transaction
                $existingTransaction = Adjustment::where('tran_id', $transaction['TranId'])->first();
                if ($existingTransaction) {
                    Log::warning('Duplicate TranId detected', [
                        'TranId' => $transaction['TranId'],
                    ]);

                    return $this->buildErrorResponse(StatusCode::DuplicateTransaction); // Return duplicate transaction error
                }

                // Process adjustment (add or subtract balance)
                $this->processTransfer(
                    $transaction['Amount'] > 0 ? User::adminUser() : $player,
                    $transaction['Amount'] > 0 ? $player : User::adminUser(),
                    TransactionName::BuyIn,
                    abs($transaction['Amount'])
                );

                // Update balance and log the adjustment
                // $player->wallet->refreshBalance();
                // $newBalance = $player->balanceFloat;
                $request->getMember()->wallet->refreshBalance();

                $newBalance = $request->getMember()->balanceFloat;

                Adjustment::create([
                    'user_id' => $player->id,
                    'operator_id' => $transaction['OperatorId'],
                    'request_date_time' => $transaction['RequestDateTime'],
                    'signature' => $transaction['Signature'],
                    'player_id' => $transaction['PlayerId'],
                    'currency' => $transaction['Currency'],
                    'tran_id' => $transaction['TranId'],
                    'amount' => $transaction['Amount'],
                    'tran_date_time' => $transaction['TranDateTime'],
                    'remark' => $transaction['Remark'],
                ]);

                Log::info('Adjustment transaction processed successfully', ['TranId' => $transaction['TranId']]);
            }

            DB::commit();
            // Log::info('All Adjustment transactions committed successfully');

            return $this->buildSuccessResponse($newBalance);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle Adjustment', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Failed to handle Adjustment'], 500);
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
        $method = 'Adjustment';
        $tranId = $transaction['TranId'];
        $requestTime = $transaction['RequestDateTime'];
        $operatorCode = $transaction['OperatorId'];
        $secretKey = config('game.api.secret_key');
        $playerId = $transaction['PlayerId'];

        return md5($method.$tranId.$requestTime.$operatorCode.$secretKey.$playerId);
    }
}
