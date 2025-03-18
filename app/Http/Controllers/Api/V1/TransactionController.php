<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Admin\ReportTransaction;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $type = $request->get('type');

        [$from, $to] = match ($type) {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            default => [now()->startOfDay(), now()],
        };

        $transactions = Auth::user()->transactions()
            ->whereIn('transactions.type', ['withdraw', 'deposit'])
            ->whereIn('transactions.name', ['credit_transfer', 'debit_transfer'])
            ->latest()->get();

        return $this->success(TransactionResource::collection($transactions));
    }

    public function getTransactionDetails($tranId)
    {
        $operatorId = 'delightMMK';

        $url = 'https://api.sm-sspi-prod.com/api/opgateway/v1/op/GetTransactionDetails';

        // Generate the RequestDateTime in UTC
        $requestDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');

        // Generate the signature using MD5 hashing
        $secretKey = '1OMJXOf88RHKpcuT';
        $functionName = 'GetTransactionDetails';
        $signatureString = $functionName.$requestDateTime.$operatorId.$secretKey;
        $signature = md5($signatureString);

        // Prepare request payload
        $payload = [
            'OperatorId' => $operatorId,
            'RequestDateTime' => $requestDateTime,
            'Signature' => $signature,
            'TranId' => $tranId,
        ];

        try {
            // Make the POST request to the API endpoint
            $response = Http::post($url, $payload);

            // Check if the response is successful
            if ($response->successful()) {
                return $response->json(); // Return the response data as JSON
            } else {
                Log::error('Failed to get transaction details', ['response' => $response->body()]);

                return response()->json(['error' => 'Failed to get transaction details'], 500);
            }
        } catch (\Exception $e) {
            Log::error('API request error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'API request error'], 500);
        }
    }

    public function GetPlayerShanReport()
    {
        // Get the authenticated player's ID
        $user_id = Auth::id();

        // Query to get all report transactions for the authenticated user
        $userTransactions = ReportTransaction::where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();

        // Get player name
        $player = Auth::user();
        $playerName = $player ? $player->user_name : 'Unknown';

        // Calculate Total Bet Amount
        $totalBet = $userTransactions->sum('bet_amount');

        // Calculate Total Win Amount (win_lose_status = 1)
        $totalWin = $userTransactions->where('win_lose_status', 1)->sum('transaction_amount');

        // Calculate Total Lose Amount (win_lose_status = 0)
        $totalLose = $userTransactions->where('win_lose_status', 0)
            ->sum(function ($transaction) {
                return abs($transaction->transaction_amount);
            });

        // Format the response data
        $data = [
            'user_id' => $user_id,
            'player_name' => $playerName,
            'total_bet' => $totalBet,
            'total_win' => $totalWin,
            'total_lose' => $totalLose,
            'transactions' => $userTransactions,
        ];

        // Return a JSON success response using the HttpResponses trait
        return $this->success($data, 'Shan Report retrieved successfully');
    }

    public function SystemWalletTest(Request $request) {}
}
