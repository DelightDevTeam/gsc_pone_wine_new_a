<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\CancelBetRequest;
use App\Models\User;
use App\Models\Webhook\Bet;
use App\Models\Webhook\Result;
use App\Services\PlaceBetWebhookService;
use App\Traits\UseWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelBetNewVersionController extends Controller
{
    use UseWebhook;

    /**
     * Handle the cancellation of bet transactions.
     */
    public function handleCancelBet(CancelBetRequest $request): JsonResponse
    {
        $transactions = $request->getTransactions();

        DB::beginTransaction();
        try {
            foreach ($transactions as $transaction) {
                // Get the player id
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

                // Check if the transaction already exists
                $existingTransaction = Bet::where('bet_id', $transaction['BetId'])->first();

                Log::info('Checking BetId For Cancellation', ['BetId' => $transaction['BetId']]);

                // Check if there's an associated result, which prevents cancellation
                $associatedResult = Result::where('round_id', $transaction['RoundId'])->first();
                if ($associatedResult) {
                    Log::info('Cancellation not allowed - bet result already processed', [
                        'RoundId' => $transaction['RoundId'],
                    ]);

                    return $this->buildErrorResponse(StatusCode::InternalServerError); // 900500 error if result exists
                }

                // Process the cancellation if the transaction exists and is active
                if ($existingTransaction && $existingTransaction->status == 'active') {
                    Log::info('Cancelling Bet Transaction', ['TranId' => $transaction['RoundId']]);

                    // Update the existing transaction status to canceled
                    $existingTransaction->status = 'cancelled';
                    $existingTransaction->cancelled_at = now();
                    $existingTransaction->save();

                    $PlayerBalance = $request->getMember()->balanceFloat;

                    // Check for sufficient balance
                    if ($transaction['BetAmount'] > $PlayerBalance) {
                        Log::warning('Insufficient balance detected', [
                            'BetAmount' => $transaction['BetAmount'],
                            'balance' => $PlayerBalance,
                        ]);

                        return $this->buildErrorResponse(StatusCode::InsufficientBalance, $PlayerBalance);
                    }

                    // Process the bet refund
                    $this->processTransfer(
                        User::adminUser(), // Assuming admin user as the receiving party
                        $player,
                        TransactionName::Refund,
                        $transaction['BetAmount']
                    );

                    Log::info('BetCancel Transaction processed successfully', ['BetID' => $transaction['BetId']]);
                }

            }

            DB::commit();
            $Balance = $request->getMember()->balanceFloat;

            // Build a successful response with the final balance of the last player
            return $this->buildSuccessResponse($Balance);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle Bet cancellation', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Failed to handle Bet cancellation'], 500);
        }
    }

    /**
     * Build a success response.
     */
    private function buildSuccessResponse(float $newBalance): JsonResponse
    {
        return response()->json([
            'Status' => StatusCode::OK->value,
            'Description' => 'Success',
            'ResponseDateTime' => now()->format('Y-m-d H:i:s'),
            'Balance' => round($newBalance, 4),
        ]);
    }

    /**
     * Build an error response.
     */
    private function buildErrorResponse(StatusCode $statusCode, float $balance = 0): JsonResponse
    {
        return response()->json([
            'Status' => $statusCode->value,
            'Description' => $statusCode->name,
            'Balance' => round($balance, 4),
        ]);
    }

    /**
     * Generate a signature for the transaction.
     */
    private function generateSignature(array $transaction): string
    {
        $method = 'CancelBet';
        $roundId = $transaction['RoundId'];
        $betId = $transaction['BetId'];
        $requestTime = $transaction['RequestDateTime'];
        $operatorCode = $transaction['OperatorId'];
        $secretKey = config('game.api.secret_key');
        $playerId = $transaction['PlayerId'];

        return md5($method.$roundId.$betId.$requestTime.$operatorCode.$secretKey.$playerId);
    }
}
