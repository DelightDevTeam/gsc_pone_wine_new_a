<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\SlotWebhookRequest;
use App\Models\User;
use App\Services\SlotWebhookService;
use App\Services\SlotWebhookValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetBalanceController extends Controller
{
    public function getBalance(SlotWebhookRequest $request)
    {
        // Log::info('GetBalance request initiated', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // Validate the request using the SlotWebhookValidator
            // Log::info('Starting validation process');
            $validator = SlotWebhookValidator::make($request)->validate();

            if ($validator->fails()) {
                // Log::warning('Validation failed', ['response' => $validator->getResponse()]);

                return response()->json($validator->getResponse(), 200);
            } else {
                // Log::info('Validation passed, no failure detected');
            }

            // Log::info('Validation passed, preparing balance response');
            $balance = $request->getMember()->balanceFloat;

            // Use round() to ensure 4 decimal places while keeping it a number
            //$formattedBalance = number_format(round($balance, 4), 4, '.', '');
            $formattedBalance = round($balance, 4);

            $response = SlotWebhookService::buildResponse(
                StatusCode::OK,
                $formattedBalance,  // Keep it as a number but rounded to 4 decimal places
                $formattedBalance
            );

            // Log::info('Returning response', ['response' => $response]);

            DB::commit();

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('An error occurred during GetBalance', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
