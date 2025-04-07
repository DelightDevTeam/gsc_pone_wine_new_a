<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetGameProviderService
{
    public function getGameProvider()
    {
        // Retrieve values from the config file
        $config = config('game.api');
        $operatorId = $config['operator_code'];
        $secretKey = $config['secret_key'];
        $baseUrl = $config['url'];
        $functionName = 'GetGameProvider';

        // Generate RequestDateTime (UTC)
        $requestDateTime = now()->setTimezone('UTC')->format('Y-m-d H:i:s');

        // Generate MD5 Signature
        $signature = md5($functionName.$requestDateTime.$operatorId.$secretKey);

        // Construct Payload
        $payload = [
            'OperatorId' => $operatorId,
            'RequestDateTime' => $requestDateTime,
            'Signature' => $signature,
        ];

        // API URL for GetGameProvider
        $url = $baseUrl.$functionName;
        // Log the request details for debugging
        Log::info('Sending GetGameProvider API Request', [
            'url' => $url,
            'payload' => $payload,
        ]);

        // Send POST request to the API
        $response = Http::post($url, $payload);
        // Log the response for debugging
        Log::info('Received GetGameProvider API Response', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        Log::info('API Response Structure', ['response' => $response]);

        // Return the response as an array
        return $response->json();
    }
}
