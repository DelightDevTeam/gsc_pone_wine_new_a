<?php

namespace App\Services;

use App\Enums\StatusCode;
use Illuminate\Support\Facades\Log;

class SlotWebhookService
{
    public static function buildResponse(StatusCode $responseCode, $after_balance, $before_balance)
    {
        // Current DateTime for ResponseDateTime
        $responseDateTime = now()->format('Y-m-d H:i:s');

        // Use round() to ensure balance and before_balance always have 4 decimal places as numbers
        // $formattedAfterBalance = round($after_balance, 4);
        // $formattedBeforeBalance = round($before_balance, 4);

        // $formattedAfterBalance = number_format(round($after_balance, 4), 4, '.', '');
        // $formattedBeforeBalance = number_format(round($before_balance, 4), 4, '.', '');
        $formattedAfterBalance = round($after_balance, 4);
        $formattedBeforeBalance = round($before_balance, 4);

        // Map the response code to its exact description
        $description = match ($responseCode) {
            StatusCode::InvalidPlayerPassword => 'Invalid player / password',
            StatusCode::InvalidSignature => 'Invalid Signature',
            default => $responseCode->name,
        };

        // Log the response being built
        // Log::info('Building final response', [
        //     'Status' => $responseCode->value,
        //     'Description' => $description,
        //     'ResponseDateTime' => $responseDateTime,
        //     'AfterBalance' => $formattedBeforeBalance,
        //     'BeforeBalance' => $formattedAfterBalance,
        // ]);

        // Return the structured response as numbers with 4 decimal places
        return [
            'Status' => $responseCode->value,
            'Description' => $description,
            'ResponseDateTime' => $responseDateTime,
            'Balance' => $formattedAfterBalance,  // Keep as numeric value, not string
        ];
    }
}
