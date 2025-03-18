<?php

namespace App\Http\Controllers\Api\V1\Slot;

use App\Http\Controllers\Controller;
use App\Services\GetGameListByProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GetGameListByProviderController extends Controller
{
    protected $getGameListByProviderService;

    public function __construct(GetGameListByProviderService $getGameListByProviderService)
    {
        $this->getGameListByProviderService = $getGameListByProviderService;
    }

    public function fetchAndSaveGameList(Request $request)
    {
        Log::info('Incoming request to fetch and save game list.', $request->all());

        // Validate the input
        $request->validate([
            'ProviderCode' => 'required|string|max:50',
        ]);

        $providerCode = $request->input('ProviderCode');

        // Use the service to fetch the game list
        $response = $this->getGameListByProviderService->getGameListByProvider($providerCode);

        // Check if the response contains the expected data
        // if (isset($response['status']) && $response['status'] == 200 && isset($response['Game'])) {
        $games = $response['Game'];

        // Save the games data to a JSON file
        $fileName = "GameListByProvider_{$providerCode}.json";
        Storage::put("public/game_list_json/$fileName", json_encode($games, JSON_PRETTY_PRINT));

        // Return a success response with the file path
        return response()->json([
            'success' => true,
            'message' => 'Game list fetched and saved successfully.',
            'file_path' => Storage::url($fileName),
        ]);
        //}

        // Handle errors in response
        return response()->json([
            'success' => false,
            'message' => $response['Description'] ?? 'An error occurred while fetching the game list.',
        ], 400);
    }

    public function fetchGameListByProvider(Request $request)
    {
        Log::info('Incoming Request to GetGameListByProvider', $request->all());

        // Validate incoming request
        $request->validate([
            'ProviderCode' => 'required|string|max:50',
        ]);

        // Retrieve provider code from request
        $providerCode = $request->input('ProviderCode');

        // Call the service
        $response = $this->getGameListByProviderService->getGameListByProvider($providerCode);

        // Log the response structure for debugging
        //Log::info('API Response Structure', ['response' => $response]);

        // Check if the response is successful
        //if (isset($response['status']) && $response['status'] == 200) {
        return response()->json([
            'success' => true,
            'data' => $response['Game'] ?? [],
        ]);
        //}

        // return response()->json([
        //     'success' => false,
        //     'message' => $response['Description'] ?? 'An error occurred',
        // ], 400);
    }
}
