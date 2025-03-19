<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc;

use App\Enums\SlotWebhookResponseCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Api\V1\Webhook\Gsc\Traits\OptimizedBettingProcess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\SlotWebhookRequest;
use App\Models\Admin\GameType;
use App\Models\Admin\GameTypeProduct;
use App\Models\Admin\Product;
use App\Models\User;
use App\Services\Slot\SlotWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PlaceBetController extends Controller
{
    use OptimizedBettingProcess;

    public function placeBet(SlotWebhookRequest $request)
    {
        $userId = $request->getMember()->id;

        // Retry logic for acquiring the Redis lock
        $attempts = 0;
        $maxAttempts = 3;
        $lock = false;

        while ($attempts < $maxAttempts && ! $lock) {
            $lock = Redis::set("wallet:lock:$userId", true, 'EX', 15, 'NX'); // 15 seconds lock
            $attempts++;

            if (! $lock) {
                sleep(1); // Wait for 1 second before retrying
            }
        }

        if (! $lock) {
            return response()->json([
                'message' => 'Another transaction is currently processing. Please try again later.',
                'userId' => $userId,
            ], 409); // 409 Conflict
        }

        // Validate the structure of the request
        $validator = $request->check();

        if ($validator->fails()) {
            // Release Redis lock and return validation error response
            Redis::del("wallet:lock:$userId");

            return $validator->getResponse();
        }

        // Retrieve transactions from the request
        $transactions = $validator->getRequestTransactions();

        // Check if the transactions are in the expected format
        if (! is_array($transactions) || empty($transactions)) {
            Redis::del("wallet:lock:$userId");

            return response()->json([
                'message' => 'Invalid transaction data format.',
                'details' => $transactions,  // Provide details about the received data for debugging
            ], 400);  // 400 Bad Request
        }

        $before_balance = $request->getMember()->balanceFloat;
        $event = $this->createEvent($request);

        DB::beginTransaction();
        try {
            // Insert bets using chunking for better performance
            $message = $this->insertBets($transactions, $event);  // Insert bets in chunks

            // Process each transaction by transferring the amount
            foreach ($transactions as $transaction) {
                $fromUser = $request->getMember();
                $toUser = User::adminUser();

                // Fetch the rate from GameTypeProduct before calling processTransfer()
                $game_type = GameType::where('code', $transaction->GameType)->first();
                $product = Product::where('code', $transaction->ProductID)->first();
                $game_type_product = GameTypeProduct::where('game_type_id', $game_type->id)
                    ->where('product_id', $product->id)
                    ->first();
                $rate = $game_type_product->rate;

                $meta = [
                    'wager_id' => $transaction->WagerID,
                    'event_id' => $request->getMessageID(),
                    'transaction_id' => $transaction->TransactionID,
                ];

                // Call processTransfer with the correct rate
                $this->processTransfer(
                    $fromUser,
                    $toUser,
                    TransactionName::Stake,
                    $transaction->TransactionAmount,
                    $rate,  // Use the fetched rate
                    $meta
                );
            }

            // Refresh balance after transactions
            $request->getMember()->wallet->refreshBalance();
            $after_balance = $request->getMember()->balanceFloat;

            DB::commit();

            Redis::del("wallet:lock:$userId");

            // Return success response
            return SlotWebhookService::buildResponse(
                SlotWebhookResponseCode::Success,
                $after_balance,
                $before_balance
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Redis::del("wallet:lock:$userId");
            Log::error('Error during placeBet', ['error' => $e]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
