<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Operator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;
use App\Enums\UserType;
use App\Enums\TransactionName;
use Illuminate\Support\Facades\Log;
use App\Helpers\InternalApiHelper;

class ShanLaunchGameController extends Controller
{
    public function launch(Request $request, WalletService $walletService)
    {
        // 1. Validate input
        $validator = Validator::make($request->all(), [
            'member_account' => 'required|string|max:50',
            'operator_code'  => 'required|string',
            'sign'           => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $member_account = $request->member_account;
        $operator_code  = $request->operator_code;
        $sign = $request->sign;

        // 2. Lookup operator (for callback_url and secret_key)
        $operator = Operator::where('code', $operator_code)
            ->where('active', true)
            ->first();
        if (!$operator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid operator code',
            ], 403);
        }
        $callbackUrl = $operator->callback_url;
        $secret_key  = $operator->secret_key;

        // 3. Signature check
        $expected_sign = md5($operator_code . $member_account . $secret_key);
        if ($sign !== $expected_sign) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Signature invalid',
            ], 403);
        }

        // 4. Call client getbalance API
        $request_time = time();
        $getbalance_sign = md5($operator_code . $request_time . 'getbalance' . $secret_key);
        $payload = [
            'batch_requests' => [
                [
                    'member_account' => $member_account,
                    'product_code'   => 100200 // Or as agreed
                ]
            ],
            'operator_code' => $operator_code,
            'currency'      => 'MMK',
            'request_time'  => $request_time,
            'sign'          => $getbalance_sign,
        ];

        $balance = 0;
        try {
            $response = Http::timeout(5)->post($callbackUrl, $payload);
            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['data'][0]['balance'])) {
                    $balance = (float) $json['data'][0]['balance'];
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Could not get balance from client.',
            ], 500);
        }

        // 5. Check if member exists, create if not, update balance
        DB::beginTransaction();
        $user = User::where('user_name', $member_account)->first();
        if (!$user) {
            $user = User::create([
                'user_name' => $member_account,
                'name'      => $member_account,
                'password'  => bcrypt('defaultpassword'),
                'type'      => UserType::Player,
            ]);
            // Set user balance using wallet service
            if ($balance > 0) {
                $walletService->deposit($user, $balance, TransactionName::CreditTransfer, ['from_launchgame_api' => true]);
            }
        } else {
            // Sync balance with Laravel wallet: set to $balance (force adjust)
            $currentBalance = $user->balanceFloat;
            if ($currentBalance < $balance) {
                $walletService->deposit($user, $balance - $currentBalance, TransactionName::CreditTransfer, ['from_launchgame_api' => true]);
            } elseif ($currentBalance > $balance) {
                $walletService->withdraw($user, $currentBalance - $balance, TransactionName::DebitTransfer, ['from_launchgame_api' => true]);
            }
        }
        DB::commit();

        // 6. Build launch game URL
        $launchGameUrl = 'https://goldendragon7.pro/?user_name=' . urlencode($member_account) . '&balance=' . $balance;

        return response()->json([
            'status' => 'success',
            'launch_game_url' => $launchGameUrl
        ]);
    }
}
