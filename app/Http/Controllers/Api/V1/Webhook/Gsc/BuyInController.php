<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc;

use App\Enums\SlotWebhookResponseCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Api\V1\Webhook\Gsc\Traits\UseWebhook;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\WebhookRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Slot\SlotWebhookService;
use App\Services\Slot\SlotWebhookValidator;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class BuyInController extends Controller
{
    use UseWebhook;

    public function buyIn(WebhookRequest $request)
    {
        DB::beginTransaction();
        try {
            $validator = $request->check();

            if ($validator->fails()) {
                return $validator->getResponse();
            }

            $before_balance = $request->getMember()->balanceFloat;

            $event = $this->createEvent($request);

            $seamless_transactions = $this->createWagerTransactions($validator->getRequestTransactions(), $event);

            foreach ($seamless_transactions as $seamless_transaction) {
                $this->processTransfer(
                    $request->getMember(),
                    User::adminUser(),
                    TransactionName::BuyIn,
                    $seamless_transaction->transaction_amount,
                    $seamless_transaction->rate,
                    [
                        'wager_id' => $seamless_transaction->wager_id,
                        'event_id' => $request->getMessageID(),
                        'transaction_id' => $seamless_transaction->id,
                    ]
                );
            }

            $request->getMember()->wallet->refreshBalance();

            $after_balance = $request->getMember()->balanceFloat;

            DB::commit();

            return SlotWebhookService::buildResponse(
                SlotWebhookResponseCode::Success,
                $after_balance,
                $before_balance
            );
        } catch (\Exception $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'Duplicate transaction detected')) {
                Log::info('Returning duplicate transaction response', [
                    'transaction_id' => $request->getTransactions()[0]['TransactionID'] ?? 'unknown',
                ]);
                return SlotWebhookService::buildResponse(
                    SlotWebhookResponseCode::DuplicateTransaction,
                    $before_balance,
                    $before_balance
                );
            }

            Log::error('Error during buyIn', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // public function buyIn(WebhookRequest $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $validator = $request->check();

    //         if ($validator->fails()) {
    //             return $validator->getResponse();
    //         }

    //         $before_balance = $request->getMember()->balanceFloat;

    //         $event = $this->createEvent($request);

    //         $seamless_transactions = $this->createWagerTransactions($validator->getRequestTransactions(), $event);

    //         foreach ($seamless_transactions as $seamless_transaction) {
    //             $this->processTransfer(
    //                 $request->getMember(),
    //                 User::adminUser(),
    //                 TransactionName::BuyIn,
    //                 $seamless_transaction->transaction_amount,
    //                 $seamless_transaction->rate,
    //                 [
    //                     'wager_id' => $seamless_transaction->wager_id,
    //                     'event_id' => $request->getMessageID(),
    //                     'transaction_id' => $seamless_transaction->id,
    //                 ]
    //             );
    //         }

    //         $request->getMember()->wallet->refreshBalance();

    //         $after_balance = $request->getMember()->balanceFloat;

    //         DB::commit();

    //         return SlotWebhookService::buildResponse(
    //             SlotWebhookResponseCode::Success,
    //             $after_balance,
    //             $before_balance
    //         );
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }
}
