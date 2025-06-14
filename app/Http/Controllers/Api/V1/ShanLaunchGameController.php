<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Operator;
use Illuminate\Support\Facades\Log;
use App\Enums\UserType;

class ShanLaunchGameController extends Controller
{
    public function launch(Request $request)
    {
        // Validate input is required 
        $validator = Validator::make($request->all(), [
            'member_account' => 'required|string|max:50',
            'operator_code'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $member_account = $request->member_account;
        $operator_code = $request->operator_code;
        $banker_account = 'banker'; // or as you prefer
        $default_player_account = 'P010101'; // or as you prefer

        // Lookup operator to get callback_url and secret_key
        $operator = Operator::where('code', $operator_code)
                            ->where('active', true)
                            ->first();

        if (!$operator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid operator code',
            ], 403);
        }

        $callbackUrl = $operator->callback_url ?? 'https://a1yoma.online/api/shan/balance';
        $secret_key = $operator->secret_key;

        // Ensure member, banker, and default player accounts exist
        $accounts = [
            $member_account,
            $banker_account,
            $default_player_account
        ];

        DB::beginTransaction();
        foreach ($accounts as $acc) {
            $user = User::where('user_name', $acc)->first();
            if (!$user) {
                User::create([
                    'user_name' => $acc,
                    'name'           => $acc,
                    'password'       => bcrypt('defaultpassword'),
                    'type'           => UserType::Player,
                ]);
            }
        }
        DB::commit();

        // Call client's getbalance API for requested player
        $request_time = time();
        $sign = md5($operator_code . $request_time . 'getbalance' . $secret_key);
        $payload = [
            'batch_requests' => [
                [
                    'member_account' => $member_account,
                    'product_code'   => 100200 // or as required
                ]
            ],
            'operator_code' => $operator_code,
            'currency'      => 'MMK',
            'request_time'  => $request_time,
            'sign'          => $sign,
        ];

        $balance = 0;
        try {
            $response = Http::timeout(5)->post($callbackUrl, $payload);
            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['data'][0]['balance'])) {
                    $balance = $json['data'][0]['balance'];
                }
            }
        } catch (\Exception $e) {
            $balance = 0;
        }

        // Build launch game URL
        $launchGameUrl = 'https://goldendragon7.pro/?user_name=' . urlencode($member_account) . '&balance=' . $balance;

        return response()->json([
            'status' => 'success',
            'launch_game_url' => $launchGameUrl
        ]);
    }
}
