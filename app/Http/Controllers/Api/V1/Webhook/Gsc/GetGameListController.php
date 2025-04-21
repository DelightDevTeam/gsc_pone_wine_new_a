<?php

namespace App\Http\Controllers\Api\V1\Webhook\Gsc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetGameListController extends Controller
{
    public function getGameList(Request $request)
    {
        try {
            // Retrieve configuration values
            $config = config('game.api');
            $operatorCode = $config['operator_code'];
            $secretKey = $config['secret_key'];
            $apiUrl = $config['url'];

            // Validate required request parameters (removed IPAddress from validation)
            $validated = $request->validate([
                'MemberName' => 'required|string',
                'ProductID' => 'required|integer',
                'GameType' => 'required|integer',
                'LanguageCode' => 'required|integer',
                'Platform' => 'required|integer',
            ]);

            // Prepare request parameters
            $requestTime = now()->format('YmdHis'); // Format: yyyyMMddHHmmss
            $methodName = 'getgamelist'; // Method name in lowercase as per docs

            $params = [
                'OperatorCode' => $operatorCode,
                'MemberName' => $request->input('MemberName'),
                'DisplayName' => $request->input('DisplayName', ''), // Optional
                'ProductID' => $request->input('ProductID'),
                'GameType' => $request->input('GameType'),
                'LanguageCode' => $request->input('LanguageCode'),
                'Platform' => $request->input('Platform'),
                'IPAddress' => $request->ip(), // Set IP address from the request
                'RequestTime' => $requestTime,
            ];

            // Generate the signature: MD5(OperatorCode + RequestTime + MethodName + SecretKey)
            $signatureString = $operatorCode.$requestTime.$methodName.$secretKey;
            $sign = md5($signatureString);

            // Add the signature to the parameters
            $params['Sign'] = $sign;

            // Make the API request
            $response = Http::post("{$apiUrl}/Seamless/GetGameList", $params);

            // Check if the request was successful
            if ($response->successful()) {
                $responseData = $response->json();

                return response()->json($responseData, 200);
            } else {
                Log::error('GetGameList API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Failed to retrieve game list',
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error in GetGameListController', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }
}
