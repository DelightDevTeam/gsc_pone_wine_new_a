<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetGameListByProviderService
{
    public function getGameListByProvider($providerCode)
    {
        // Retrieve configuration from config file
        $config = config('game.api');
        $operatorId = $config['operator_code'];
        $secretKey = $config['secret_key'];
        $baseUrl = $config['url'];
        $functionName = 'GetGameListByProvider';

        $requestDateTime = now()->setTimezone('UTC')->format('Y-m-d H:i:s');

        // Generate MD5 Signature
        $signature = md5($functionName.$requestDateTime.$operatorId.$secretKey);

        // Construct payload
        $payload = [
            'OperatorId' => $operatorId,
            'RequestDateTime' => $requestDateTime,
            'Signature' => $signature,
            'ProviderCode' => $providerCode,
        ];

        // API URL for GetGameListByProvider
        $url = $baseUrl.$functionName;

        // Log::info('Sending GetGameListByProvider API Request', [
        //     'url' => $url,
        //     'payload' => $payload,
        // ]);

        // Send the POST request to the API
        $response = Http::post($url, $payload);

        // Log the response for debugging
        // Log::info('Received GetGameListByProvider API Response', [
        //     'status' => $response->status(),
        //     'response' => $response->json(),
        // ]);

        // Return the response as an array
        return $response->json();
    }
}
