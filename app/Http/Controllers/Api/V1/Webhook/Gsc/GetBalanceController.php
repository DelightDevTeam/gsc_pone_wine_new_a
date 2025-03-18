<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc;


use App\Http\Controllers\Controller;
use App\Http\Requests\Gsc\GetBalanceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GetBalanceController extends Controller
{
    /**
     * Handle the GetBalance request.
     *
     * @param GetBalanceRequest $request
     * @return JsonResponse
     */
    public function getBalance(GetBalanceRequest $request): JsonResponse
    {
        // Validate the request signature
        if (!$request->validateSignature()) {
            return response()->json([
                'ErrorCode' => 1,
                'ErrorMessage' => 'Invalid signature',
            ], 401);
        }

        // Retrieve the member by MemberName
        $member = User::where('user_name', $request->getMemberName())->first();

        // If member not found, return an error response
        if (!$member) {
            return response()->json([
                'ErrorCode' => 2,
                'ErrorMessage' => 'Member not found',
            ], 404);
        }

        // Return the balance information
        return response()->json([
            'ErrorCode' => 0,
            'ErrorMessage' => 'Success',
            'Balance' => $member->balance, // Assuming 'balance' is a field in the User model
            'BeforeBalance' => $member->before_balance, // Assuming 'before_balance' is a field in the User model
        ]);
    }
}