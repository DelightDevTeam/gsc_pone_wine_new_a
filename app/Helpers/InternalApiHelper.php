<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class InternalApiHelper
{
    // Store your header key in .env or config for security
    public static function getTransactionKey(): string
    {
        return config('services.shan.transaction_key', env('TRANSACTION_KEY', 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr'));
    }

    /**
     * POST to a given internal endpoint with transaction header.
     */
    public static function postWithTransactionKey(string $url, array $data)
    {
        return Http::withHeaders([
            'X-Transaction-Key' => self::getTransactionKey(),
        ])->post($url, $data);
    }

    /**
     * GET with transaction header (if you need)
     */
    public static function getWithTransactionKey(string $url, array $query = [])
    {
        return Http::withHeaders([
            'X-Transaction-Key' => self::getTransactionKey(),
        ])->get($url, $query);
    }
}
