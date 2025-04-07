<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc;

use App\Enums\SlotWebhookResponseCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\SlotWebhookRequest;
use App\Services\Slot\SeamlessTransactionWebhookValidator;
use App\Services\Slot\SlotWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetBalanceController extends Controller
{
    public function getBalance(SlotWebhookRequest $request)
    {
        DB::beginTransaction();
        try {
            $validator = SeamlessTransactionWebhookValidator::make($request)->validate();

            if ($validator->fails()) {
                Log::warning('GetBalanceController: Validation failed', [
                    'errors' => $validator->getResponse(),
                    'method' => $request->getMethodName(),
                    'operator_code' => $request->getOperatorCode(),
                    'secret_key' => config('game.api.secret_key'),
                    'api_url' => config('game.api.url'),
                ]);

                return $validator->getResponse();
            }

            $balance = $request->getMember()->balanceFloat;

            DB::commit();

            return SlotWebhookService::buildResponse(
                SlotWebhookResponseCode::Success,
                $balance,
                $balance
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
