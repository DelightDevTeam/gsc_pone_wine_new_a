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

class PushBetController extends Controller
{
    use UseWebhook;

    public function pushBet(WebhookRequest $request)
{
    DB::beginTransaction();
    try {
        $validator = $request->check();

        if ($validator->fails()) {
            Log::info('PushBet validator failed', ['response' => $validator->getResponse()]);
            return $validator->getResponse();
        }

        $before_balance = $request->getMember()->balanceFloat;

        $event = $this->createEvent($request);

        Log::info('PushBet processing', [
            'wager_id' => $validator->getRequestTransactions()[0]->WagerID,
            'transaction_id' => $validator->getRequestTransactions()[0]->TransactionID,
            'user_id' => $request->getMember()->id,
        ]);

        $seamlessTransactions = $this->createWagerTransactions($validator->getRequestTransactions(), $event);

        $request->getMember()->wallet->refreshBalance();
        $after_balance = $request->getMember()->balanceFloat;

        Log::info('PushBet completed', [
            'seamless_transaction_ids' => array_map(fn($t) => $t->id, $seamlessTransactions),
            'before_balance' => $before_balance,
            'after_balance' => $after_balance,
        ]);

        DB::commit();

        return SlotWebhookService::buildResponse(
            SlotWebhookResponseCode::Success,
            $after_balance,
            $before_balance
        );
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error during PushBet', [
            'error' => $e->getMessage(),
            'wager_id' => $validator->getRequestTransactions()[0]->WagerID ?? null,
        ]);
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}
    // public function pushBet(WebhookRequest $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $validator = $request->check();

    //         if ($validator->fails()) {
    //             return $validator->getResponse();
    //         }

    //         $before_balance = $request->getMember()->balanceFloat;

    //         $event = $this->createEvent($request);

    //         $this->createWagerTransactions($validator->getRequestTransactions(), $event);

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