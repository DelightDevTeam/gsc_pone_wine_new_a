<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Operator;
use App\Models\Admin\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;


class ShanGetBalanceController extends Controller
{
    public function shangetbalance(Request $request)
    {
        // 1. Validate input structure
        $validator = Validator::make($request->all(), [
            'batch_requests' => 'required|array|min:1',
            'batch_requests.*.member_account' => 'required|string|max:50',
            'batch_requests.*.product_code' => 'required|integer',
            'batch_requests.*.balance' => 'required|numeric', // balance comes from external!
            'operator_code' => 'required|string',
            'currency' => 'required|string',
            'request_time' => 'required|integer',
            'sign' => 'required|string',
        ]);

        Log::info('request', $request->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Check operator_code in database
        $operator = Operator::where('code', $request->operator_code)
                            ->where('active', true)
                            ->first();
        Log::info('operator', $operator->toArray());
        if (!$operator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid or inactive operator_code',
            ], 403);
        }

        // 3. Signature check using operator's secret_key from DB
        $secret_key = $operator->secret_key;
        $expectedSign = md5($request->operator_code . $request->request_time . 'getbalance' . $secret_key);
        Log::info('expectedSign', $expectedSign);
        if ($request->sign !== $expectedSign) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Signature invalid',
            ], 403);
        }

        // 4. Validate product_codes from DB
        $allowed_product_codes = Product::where('status', true)->pluck('code')->toArray();

        $callbackUrl = $operator->callback_url ?? 'https://a1yoma.online/api/shan/balance';

    $results = [];
    foreach ($request->batch_requests as $item) {
        // Build the payload for the callback
        $callbackPayload = [
            'member_account' => $item['member_account'],
            'product_code'   => $item['product_code'],
        ];

        // Optionally, add security (sign, operator_code, etc) as required by the customer
        // Example:
        // $callbackPayload['operator_code'] = $operator->code;
        // $callbackPayload['sign'] = md5(...);

        // Make the actual request to customer server
        try {
            $response = Http::timeout(5)->post($callbackUrl, $callbackPayload);
            if ($response->successful()) {
                $callbackData = $response->json();
                $balance = $callbackData['balance'] ?? null;
                $status  = 'success';
            } else {
                $balance = null;
                $status  = 'callback_failed';
            }
        } catch (\Exception $e) {
            $balance = null;
            $status  = 'callback_exception';
        }

        $results[] = [
            'member_account' => $item['member_account'],
            'product_code'   => $item['product_code'],
            'balance'        => $balance,
            'currency'       => $request->currency,
            'status'         => $status,
        ];
    }

    return response()->json([
        'status' => 'success',
        'data' => $results
    ]);
        
    }

    

}
