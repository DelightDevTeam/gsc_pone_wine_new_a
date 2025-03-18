<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\StatusCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\BetNResultWebhookRequest;
use App\Models\User;
use App\Models\Webhook\BetNResult;
use App\Services\PlaceBetWebhookService;
use App\Services\Webhook\BetNResultWebhookValidator;
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
        DB::beginTransaction();
        try {
            // Log::info('Starting handleBetNResult method');

            // Validate player
            $player = $request->getMember();
            if (! $player) {
                // Log::warning('Invalid player detected', [
                //     'PlayerId' => $request->getPlayerId(),
                // ]);

                // Return Invalid Player response
                return PlaceBetWebhookService::buildResponse(
                    StatusCode::InvalidPlayerPassword,
                    0, // Balance is 0 in case of invalid player
                    0
                );
            }

            $oldBalance = $request->getMember()->balanceFloat;
            // Log::info('Retrieved player balance', ['old_balance' => $oldBalance]);

            // Perform validation using the validator class
            //$validator = BetNResultWebhookValidator::make($request)->validate();
            $validator = $request->check();
            // Log::info('Validator check passed');
            if ($validator->fails()) {
                // Log::warning('Validation failed');

                return $this->buildErrorResponse(StatusCode::InvalidSignature);
            }

            // Check for sufficient balance
            if ($request->getBetAmount() > $oldBalance) {
                Log::warning('Insufficient balance detected', [
                    'bet_amount' => $request->getBetAmount(),
                    'balance' => $oldBalance,
                ]);

                return $this->buildErrorResponse(StatusCode::InsufficientBalance, $oldBalance);
            }

            // Check for duplicate TranId
            $existingTransaction = BetNResult::where('tran_id', $request->getTranId())->first();
            Log::info('Retrieved player TransactionID', ['TransactionID' => $existingTransaction ? $existingTransaction->tran_id : 'No transaction found']);

            DB::enableQueryLog();
            $existingTransaction = BetNResult::where('tran_id', $request->getTranId())->first();
            Log::info('Query log:', DB::getQueryLog());

            if ($existingTransaction) {
                Log::warning('Duplicate TranId detected', ['tran_id' => $request->getTranId()]);

                return $this->buildErrorResponse(StatusCode::DuplicateTransaction, $oldBalance);
            }

            // Process transfer using the processTransfer trait method
            $this->processTransfer(
                $player,
                User::adminUser(), // Assuming admin user as the receiving party
                TransactionName::Stake, // Using TransactionName enum for transaction type
                $request->getBetAmount()
            );

            $request->getMember()->wallet->refreshBalance();

            $newBalance = $request->getMember()->balanceFloat;

            $tranDateTime = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $request->getTranDateTime())->format('Y-m-d H:i:s');

            Log::info('Storing transaction with TranId', ['TranId' => $request->getTranId()]);

            // Create the transaction record
            BetNResult::create([
                'user_id' => $request->getUserId(),
                'operator_id' => $request->getOperatorId(),
                'request_date_time' => $request->getRequestDateTime(),
                'signature' => $request->getSignature(),
                'player_id' => $request->getPlayerId(),
                'currency' => $request->getCurrency(),
                'tran_id' => $request->getTranId(),
                'game_code' => $request->getGameCode(),
                'bet_amount' => $request->getBetAmount(),
                'win_amount' => $request->getWinAmount(),
                'net_win' => $request->getNetWin(),
                'tran_date_time' => $tranDateTime,
                'auth_token' => $request->getAuthToken(),
                //'old_balance' => round($BeforeBalance, 4),
                //'new_balance' => round($newBalance, 4),
            ]);

            Log::info('Transaction created successfully', ['new_balance' => $newBalance]);

            DB::commit();
            // Log::info('Transaction committed successfully');

            // Build a successful response
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

    /**
     * Builds a success response.
     */
    private function buildSuccessResponse(float $newBalance): JsonResponse
    {
        return response()->json([
            'Status' => StatusCode::OK->value,
            'Description' => 'OK',
            //'BeforeBalance' => round($oldBalance, 4),
            'Balance' => round($newBalance, 4),
        ]);
    }

    /**
     * Builds an error response with a given status code.
     */
    private function buildErrorResponse(StatusCode $statusCode, float $balance = 0): JsonResponse
    {
        return response()->json([
            'Status' => $statusCode->value,
            'Description' => $statusCode->name,
            'BeforeBalance' => round($balance, 4),
            'AfterBalance' => round($balance, 4),
        ]);
    }
}
