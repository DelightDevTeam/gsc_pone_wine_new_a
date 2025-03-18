<?php

return [

    'api' => [
        'operator_code' => env('SEAMLESS_OPERATOR_ID'),  // Using SEAMLESS_OPERATOR_ID from .env
        // 'password' => env('SEAMLESS_PASSWORD'),          // Using SEAMLESS_PASSWORD from .env (if required)
        'secret_key' => env('SEAMLESS_SECRET_KEY'),      // Using SEAMLESS_SECRET_KEY from .env
        'url' => env('SEAMLESS_API_URL'),                // Using SEAMLESS_API_URL from .env
        'currency' => env('SEAMLESS_CURRENCY'),          // Using SEAMLESS_CURRENCY from .env
    ],

];
