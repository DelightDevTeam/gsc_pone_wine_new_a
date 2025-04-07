<?php

namespace App\Http\Controllers\Api\V1\Slot;

use App\Http\Controllers\Controller;
use App\Services\GetGameProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetGameProviderController extends Controller
{
    protected $getGameProviderService;

    public function __construct(GetGameProviderService $getGameProviderService)
    {
        $this->getGameProviderService = $getGameProviderService;
    }

    public function fetchGameProviders(Request $request)
    {
        Log::info('Incoming Request to GetGameProvider', $request->all());

        $response = $this->getGameProviderService->getGameProvider();

        // Explicitly log and check the response
        Log::info('API Response Structure', ['response' => $response]);

        // Check for status and GameProviders key
        // if (is_array($response) && isset($response['status']) && $response['status'] == 200) {
        //     if (isset($response['GameProviders']) && is_array($response['GameProviders'])) {
        Log::info('GameProviders Data', ['data' => $response['GameProviders']]);

        return response()->json([
            'success' => true,
            'data' => $response['GameProviders'],
        ]);
        // }

        // Handle missing GameProviders data
        return response()->json([
            'success' => false,
            'message' => 'GameProviders data is missing or invalid',
        ], 400);
        // }

        // Handle general API error
        return response()->json([
            'success' => false,
            'message' => $response['Description'] ?? 'An unknown error occurred',
        ], 400);
    }

    //     public function fetchGameProviders(Request $request)
    // {
    //     Log::info('Incoming Request to GetGameProvider', $request->all());

    //     $response = $this->getGameProviderService->getGameProvider();

    //     // Explicitly log and check the response
    //     Log::info('API Response Structure', ['response' => $response]);

    //     // Check for status and GameProviders key
    //     if (is_array($response) && isset($response['status']) && $response['status'] == 200) {
    //         if (isset($response['GameProviders']) && is_array($response['GameProviders'])) {
    //             Log::info('GameProviders Data', ['data' => $response['GameProviders']]);
    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $response['GameProviders'],
    //             ]);
    //         }

    //         // Handle missing GameProviders data
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'GameProviders data is missing or invalid',
    //         ], 400);
    //     }

    //     // Handle general API error
    //     return response()->json([
    //         'success' => false,
    //         'message' => $response['Description'] ?? 'An unknown error occurred',
    //     ], 400);
    // }

    //     public function fetchGameProviders(Request $request)
    // {
    //     //Log::info('Incoming Request to GetGameProvider', $request->all());

    //     $response = $this->getGameProviderService->getGameProvider();

    //     //Log::info('API Response Structure', ['response' => $response]);
    //     Log::info('GameProviders Data', ['data' => $response['GameProviders'] ?? 'No Data Found']);

    //     if (isset($response['status']) && $response['status'] == 200) {
    //         if (isset($response['GameProviders'])) {
    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $response['GameProviders'],
    //             ]);
    //         }

    //         // Handle missing GameProviders data
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'GameProviders data is missing',
    //         ], 400);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => $response['Description'] ?? 'An unknown error occurred',
    //     ], 400);
    // }

    //     public function fetchGameProviders(Request $request)
    // {
    //     Log::info('Incoming Request to GetGameProvider', $request->all());

    //     $response = $this->getGameProviderService->getGameProvider();

    //     Log::info('API Response Structure', ['response' => $response]);

    //     if (isset($response['status']) && $response['status'] == 200) {
    //         return response()->json([
    //             'success' => true,
    //             'data' => $response['GameProviders'] ?? [],
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => $response['Description'] ?? 'An error occurred',
    //     ], 400);
    // }

    // public function fetchGameProviders(Request $request)
    // {
    //      Log::info('Incoming Request to GetGameProvider', $request->all());
    //     $response = $this->getGameProviderService->getGameProvider();

    //     if (isset($response['ErrorCode']) && $response['ErrorCode'] == 0) {
    //         return response()->json([
    //             'success' => true,
    //             'data' => $response['Data'],
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => $response['ErrorMessage'] ?? 'An error occurred',
    //     ], 400);
    // }
}
