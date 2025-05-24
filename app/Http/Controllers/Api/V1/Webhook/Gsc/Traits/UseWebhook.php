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
use App\Models\SeamlessTransaction;
use App\Models\User;
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
            // Generate a default transaction_id if null
            $transactionId = $transaction->TransactionID ?? 'pushbet_'.$transaction->WagerID.'_'.uniqid();

            // Check for duplicate transaction_id
            $existingTransaction = SeamlessTransaction::where('transaction_id', $transactionId)->first();
            if ($existingTransaction) {
                Log::warning('Duplicate transaction_id detected in createWagerTransactions', [
                    'transaction_id' => $transactionId,
                    'wager_id' => $transaction->WagerID,
                ]);
                throw new \Exception('Duplicate transaction detected: '.$transactionId);
            }

            // Fetch game type and product for rate
            $game_type = GameType::where('code', $transaction->GameType ?? '0')->first();
            if (! $game_type) {
                Log::error('Game type not found', ['game_type' => $transaction->GameType]);
                throw new \Exception("Game type not found for {$transaction->GameType}");
            }

            $product = Product::where('code', $transaction->ProductID)->first();
            if (! $product) {
                Log::error('Product not found', ['product_id' => $transaction->ProductID]);
                throw new \Exception("Product not found for {$transaction->ProductID}");
            }

            $game_type_product = GameTypeProduct::where('game_type_id', $game_type->id)
                ->where('product_id', $product->id)
                ->first();
            $rate = $game_type_product->rate ?? 1;

            // Log transaction creation
            // Log::info('Creating SeamlessTransaction', [
            //     'wager_id' => $transaction->WagerID,
            //     'transaction_id' => $transactionId,
            //     'user_id' => $userId,
            //     'seamless_event_id' => $seamlessEventId,
            // ]);

            $seamlessTransactions[] = SeamlessTransaction::create([
                'user_id' => $userId,
                'wager_id' => $transaction->WagerID,
                'game_type_id' => $game_type->id,
                'product_id' => $product->id,
                'transaction_id' => $transactionId,
                'rate' => $rate,
                'transaction_amount' => $transaction->TransactionAmount ?? 0,
                'payout_amount' => $transaction->PayoutAmount ?? 0,
                'bet_amount' => $transaction->BetAmount ?? 0,
                'valid_bet_amount' => $transaction->ValidBetAmount ?? 0,
                'status' => $transaction->Status ?? TransactionStatus::Pending,
                'wager_status' => ($transaction->TransactionAmount ?? 0) > 0 ? WagerStatus::Win : WagerStatus::Ongoing,
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
   
}
