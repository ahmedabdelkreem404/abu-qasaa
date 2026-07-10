<?php

return [
    'api_key' => env('PAYMOB_API_KEY'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'iframe_id' => env('PAYMOB_IFRAME_ID'),
    'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
    'base_url' => rtrim(env('PAYMOB_BASE_URL', 'https://accept.paymob.com/api'), '/'),
    'callback_url' => env('PAYMOB_CALLBACK_URL'),
    'return_url' => env('PAYMOB_RETURN_URL'),
    'currency' => env('PAYMOB_CURRENCY', 'EGP'),
    'fake_mode' => (bool) env('PAYMOB_FAKE_MODE', false),
    'timeout' => 15,
];
