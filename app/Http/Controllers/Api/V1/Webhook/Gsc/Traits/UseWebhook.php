<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc\Traits;

use App\Enums\TransactionName;
use App\Enums\TransactionStatus;
use App\Enums\WagerStatus;
use App\Http\Requests\Slot\WebhookRequest;
use App\Models\Admin\GameType;
use App\Models\Admin\GameTypeProduct;
use App\Models\Admin\Product;
use App\Models\SeamlessEvent;
use App\Models\User;
use App\Models\SeamlessTransaction;
use App\Services\Slot\Dto\RequestTransaction;
use App\Services\WalletService;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait UseWebhook
{
    
    public function createEvent(WebhookRequest $request): SeamlessEvent
    {
        return SeamlessEvent::create([
            'user_id' => $request->getMember()->id,
            'message_id' => $request->getMessageID(),
            'product_id' => $request->getProductID(),
            'request_time' => $request->getRequestTime(),
            'raw_data' => $request->all(),
        ]);
    }

    public function createWagerTransactions(array $transactions, SeamlessEvent $event)
    {
        $seamlessTransactions = [];
        $userId = $event->user_id;
        $seamlessEventId = $event->id;

        foreach ($transactions as $transaction) {
            // Check for duplicate transaction_id
            $existingTransaction = SeamlessTransaction::where('transaction_id', $transaction->TransactionID)->first();
            if ($existingTransaction) {
                Log::warning('Duplicate transaction_id detected in createWagerTransactions', [
                    'transaction_id' => $transaction->TransactionID,
                    'wager_id' => $transaction->WagerID,
                ]);
                throw new \Exception('Duplicate transaction detected: ' . $transaction->TransactionID);
            }

            // Assuming rate is fetched or set elsewhere; for now, we'll set a default rate
            $rate = 1; // Replace with actual rate logic if needed

            $seamlessTransactions[] = SeamlessTransaction::create([
                'user_id' => $userId,
                'wager_id' => $transaction->WagerID,
                'game_type_id' => $transaction->GameType ?? 0, // Adjust as needed
                'product_id' => $transaction->ProductID,
                'transaction_id' => $transaction->TransactionID,
                'rate' => $rate,
                'transaction_amount' => $transaction->TransactionAmount,
                'payout_amount' => $transaction->PayoutAmount ?? 0,
                'bet_amount' => $transaction->BetAmount ?? 0,
                'valid_bet_amount' => $transaction->ValidBetAmount ?? 0,
                'status' => $transaction->Status,
                'wager_status' => $transaction->TransactionAmount > 0 ? WagerStatus::Win : WagerStatus::Lose,
                'seamless_event_id' => $seamlessEventId,
                'member_name' => $transaction->MemberName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $seamlessTransactions;
    }

    public function processTransfer(User $from, User $to, TransactionName $transactionName, float $amount, int $rate, array $meta)
    {
        $walletService = app(WalletService::class);
        $walletService->transfer($from, $to, abs($amount), $transactionName, $meta);
    }
    // public function createEvent(
    //     WebhookRequest $request,
    // ): SeamlessEvent {
    //     return SeamlessEvent::create([
    //         'user_id' => $request->getMember()->id,
    //         'message_id' => $request->getMessageID(),
    //         'product_id' => $request->getProductID(),
    //         'request_time' => $request->getRequestTime(),
    //         'raw_data' => $request->all(),
    //     ]);
    // }

    // /**
    //  * @param  array<int,RequestTransaction>  $requestTransactions
    //  * @return array<int, SeamlessTransaction>
    //  *
    //  * @throws MassAssignmentException
    //  */
    // public function createWagerTransactions(
    //     $requestTransactions,
    //     SeamlessEvent $event,
    //     bool $refund = false
    // ) {
    //     $seamless_transactions = [];

    //     foreach ($requestTransactions as $requestTransaction) {
    //         // Ensure $requestTransaction is an instance of RequestTransaction
    //         if ($requestTransaction instanceof \App\Services\Slot\Dto\RequestTransaction) {
    //             // Check for duplicate transaction by TransactionID
    //             $existingTransactionById = SeamlessTransaction::where('transaction_id', $requestTransaction->TransactionID)
    //                 ->first();

    //             if ($existingTransactionById) {
    //                 Log::info('Duplicate transaction found in createWagerTransactions', [
    //                     'transaction_id' => $requestTransaction->TransactionID,
    //                     'existing_id' => $existingTransactionById->id,
    //                 ]);
    //                 $seamless_transactions[] = $existingTransactionById;
    //                 continue; // Skip further processing for this transaction
    //             }

    //             DB::transaction(function () use (&$seamless_transactions, $event, $requestTransaction, $refund) {
    //                 // Check if a transaction with the same wager_id already exists
    //                 $existingTransaction = SeamlessTransaction::where('wager_id', $requestTransaction->WagerID)
    //                     ->lockForUpdate()
    //                     ->first();

    //                 if ($existingTransaction) {
    //                     // Update the existing transaction
    //                     $existingTransaction->update([
    //                         'transaction_amount' => $requestTransaction->TransactionAmount,
    //                         'payout_amount' => $requestTransaction->PayoutAmount,
    //                         'bet_amount' => $requestTransaction->BetAmount,
    //                         'valid_bet_amount' => $requestTransaction->ValidBetAmount,
    //                         'status' => $requestTransaction->Status,
    //                         'wager_status' => $requestTransaction->TransactionAmount > 0 ? WagerStatus::Win : WagerStatus::Lose,
    //                         'member_name' => $requestTransaction->MemberName,
    //                         'updated_at' => now(),
    //                     ]);

    //                     $seamless_transactions[] = $existingTransaction;
    //                 } else {
    //                     // Create a new transaction
    //                     $game_type = GameType::where('code', $requestTransaction->GameType)->first();

    //                     if (! $game_type) {
    //                         throw new Exception("Game type not found for {$requestTransaction->GameType}");
    //                     }

    //                     $product = Product::where('code', $requestTransaction->ProductID)->first();

    //                     if (! $product) {
    //                         throw new Exception("Product not found for {$requestTransaction->ProductID}");
    //                     }

    //                     $game_type_product = GameTypeProduct::where('game_type_id', $game_type->id)
    //                         ->where('product_id', $product->id)
    //                         ->first();

    //                     $rate = 1; // Default rate, replace with actual logic if needed

    //                     $seamless_transactions[] = $event->transactions()->create([
    //                         'user_id' => $event->user->id,
    //                         'wager_id' => $requestTransaction->WagerID,
    //                         'game_type_id' => $requestTransaction->GameType,
    //                         'product_id' => $requestTransaction->ProductID,
    //                         'transaction_id' => $requestTransaction->TransactionID,
    //                         'rate' => $rate,
    //                         'transaction_amount' => $requestTransaction->TransactionAmount,
    //                         'payout_amount' => $requestTransaction->PayoutAmount,
    //                         'bet_amount' => $requestTransaction->BetAmount,
    //                         'valid_bet_amount' => $requestTransaction->ValidBetAmount,
    //                         'status' => $requestTransaction->Status,
    //                         'wager_status' => $requestTransaction->TransactionAmount > 0 ? WagerStatus::Win : WagerStatus::Lose,
    //                         'seamless_event_id' => $event->id,
    //                         'member_name' => $requestTransaction->MemberName,
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ]);
    //                 }
    //             }, 3); // Retry 3 times if deadlock occurs
    //         } else {
    //             throw new \Exception('Invalid transaction data format.');
    //         }
    //     }

    //     return $seamless_transactions;
    // }

    // public function processTransfer(User $from, User $to, TransactionName $transactionName, float $amount, int $rate, array $meta)
    // {
    //     app(WalletService::class)
    //         ->transfer(
    //             $from,
    //             $to,
    //             abs($amount),
    //             $transactionName,
    //             $meta
    //         );
    // }
}