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

trait UseWebhook
{
    public function createEvent(
        WebhookRequest $request,
    ): SeamlessEvent {
        return SeamlessEvent::create([
            'user_id' => $request->getMember()->id,
            'message_id' => $request->getMessageID(),
            'product_id' => $request->getProductID(),
            'request_time' => $request->getRequestTime(),
            'raw_data' => $request->all(),
        ]);
    }

    /**
     * @param  array<int,RequestTransaction>  $requestTransactions
     * @return array<int, SeamlessTransaction>
     *
     * @throws MassAssignmentException
     */
    public function createWagerTransactions(
        $requestTransactions,
        SeamlessEvent $event,
        bool $refund = false
    ) {
        $seamless_transactions = [];

        foreach ($requestTransactions as $requestTransaction) {
            DB::transaction(function () use (&$seamless_transactions, $event, $requestTransaction, $refund) {

                $wager = SeamlessTransaction::where('wager_id', $requestTransaction->WagerID)
                    ->lockForUpdate()
                    ->firstOrCreate([
                        'wager_id' => $requestTransaction->WagerID,
                    ], [
                        'user_id' => $event->user->id,
                        'wager_id' => $requestTransaction->WagerID,
                    ]);

                if ($refund) {
                    $wager->update([
                        'status' => WagerStatus::Refund,
                    ]);
                } elseif (! $wager->wasRecentlyCreated) {
                    $wager->update([
                        'status' => $requestTransaction->TransactionAmount > 0 ? WagerStatus::Win : WagerStatus::Lose,
                    ]);
                }

                $game_type = GameType::where('code', $requestTransaction->GameType)->first();

                if (! $game_type) {
                    throw new Exception("Game type not found for {$requestTransaction->GameType}");
                }
                $product = Product::where('code', $requestTransaction->ProductID)->first();

                if (! $product) {
                    throw new Exception("Product not found for {$requestTransaction->ProductID}");
                }

                $game_type_product = GameTypeProduct::where('game_type_id', $game_type->id)
                    ->where('product_id', $product->id)
                    ->first();

                //$rate = $game_type_product->rate;
                $rate = 1;
                $user = Auth::user(); // Get the authenticated user

                $seamless_transactions[] = $event->transactions()->create([
                     'user_id' => $event->user->id,
                            'wager_id' => $requestTransaction['WagerID'],
                            'game_type_id' => $requestTransaction['GameType'],
                            'product_id' => $requestTransaction['ProductID'],
                            'transaction_id' => $requestTransaction['TransactionID'],
                            'rate' => $rate,
                            'transaction_amount' => $requestTransaction['TransactionAmount'],
                            'payout_amount' => $requestTransaction['PayoutAmount'],
                            'bet_amount' => $requestTransaction['BetAmount'],
                            'valid_bet_amount' => $requestTransaction['ValidBetAmount'],
                            'status' => $requestTransaction['Status'],
                            'wager_status' => $requestTransaction['TransactionAmount'] > 0 ? WagerStatus::Win : WagerStatus::Lose,
                            'seamless_event_id' => $event->id,
                            'member_name' => $requestTransaction['MemberName'],
                            'created_at' => now(),
                            'updated_at' => now(),
                    
                ]);
            }, 3); // Retry 3 times if deadlock occurs

        }

        return $seamless_transactions;
    }

    public function processTransfer(User $from, User $to, TransactionName $transactionName, float $amount, int $rate, array $meta)
    {
        // TODO: ask: what if operator doesn't want to pay bonus
        app(WalletService::class)
            ->transfer(
                $from,
                $to,
                abs($amount),
                $transactionName,
                $meta
            );
    }
}