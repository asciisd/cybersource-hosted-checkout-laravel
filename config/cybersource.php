<?php

return [
    'profile_id' => env('CYBERSOURCE_PROFILE_ID'),
    'access_key' => env('CYBERSOURCE_ACCESS_KEY'),
    'secret_key' => env('CYBERSOURCE_SECRET_KEY'),
    'endpoint' => env('CYBERSOURCE_ENDPOINT', 'https://testsecureacceptance.cybersource.com'),

    'response_url' => env('CYBERSOURCE_RESPONSE_URL', '/cybersource/response'),
    'notification_url' => env('CYBERSOURCE_NOTIFICATION_URL', '/cybersource/notification'),
]; 