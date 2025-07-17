<?php

return [
    'profile_id' => env('CYBERSOURCE_PROFILE_ID'),
    'access_key' => env('CYBERSOURCE_ACCESS_KEY'),
    'secret_key' => env('CYBERSOURCE_SECRET_KEY'),
    'endpoint' => env('CYBERSOURCE_ENDPOINT', 'https://testsecureacceptance.cybersource.com'),

    'merchant_id' => env('CYBERSOURCE_MERCHANT_ID'),
    'api_key' => env('CYBERSOURCE_API_KEY'),
    'api_secret_key' => env('CYBERSOURCE_API_SECRET_KEY'),
    'api_host' => env('CYBERSOURCE_API_HOST', 'apitest.cybersource.com'),

    'redirect_url' => '/cybersource/redirect',
    'notification_url' => '/cybersource/notification',
];
