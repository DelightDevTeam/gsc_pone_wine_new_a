<?php

namespace App\Services;

use App\Enums\StatusCode;
use Illuminate\Support\Facades\Log;

class PlaceBetWebhookService
{
    public static function buildResponse(StatusCode $responseCode, $oldBalance, $newBalance)
    {
        // Current DateTime for ResponseDateTime
        $responseDateTime = now()->format('Y-m-d H:i:s');

        // Map the response code to its exact description
        $description = match ($responseCode) {
            StatusCode::InvalidPlayerPassword => 'Invalid player / password',
            StatusCode::InvalidSignature => 'Invalid Signature',
            default => $responseCode->name,
        };

        // Ensure OldBalance and NewBalance are rounded to 4 decimal places
        $roundedOldBalance = round($oldBalance, 4);
        $roundedNewBalance = round($newBalance, 4);

        // Log the response being built
        // Log::info('Building final response', [
        //     'Status' => $responseCode->value,
        //     'Description' => $description,
        //     'ResponseDateTime' => $responseDateTime,
        //     'OldBalance' => $roundedOldBalance,
        //     'NewBalance' => $roundedNewBalance,
        // ]);

        // Return the structured response with rounded balances
        return [
            'Status' => $responseCode->value,
            'Description' => $description,
            'ResponseDateTime' => $responseDateTime,
            'OldBalance' => $roundedOldBalance,  // Rounded to 4 decimal places
            'NewBalance' => $roundedNewBalance,  // Rounded to 4 decimal places
        ];
    }
}
// class PlaceBetWebhookService
// {
//     public static function buildResponse(StatusCode $responseCode, $oldBalance, $newBalance)
//     {
//         // Current DateTime for ResponseDateTime
//         $responseDateTime = now()->format('Y-m-d H:i:s');

//         // Map the response code to its exact description
//         $description = match ($responseCode) {
//             StatusCode::InvalidPlayerPassword => 'Invalid player / password',
//             StatusCode::InvalidSignature => 'Invalid Signature',
//             default => $responseCode->name,
//         };

//         // Log the response being built
//         Log::info('Building final response', [
//             'Status' => $responseCode->value,
//             'Description' => $description,
//             'ResponseDateTime' => $responseDateTime,
//             'OldBalance' => $oldBalance,
//             'NewBalance' => $newBalance,
//         ]);

//         // Return the structured response
//         return [
//             'Status' => $responseCode->value,
//             'Description' => $description,
//             'ResponseDateTime' => $responseDateTime,
//             'OldBalance' => $oldBalance,
//             'NewBalance' => $newBalance,
//         ];
//     }
// }
