<?php

return [
    'oauth' => [
        'auth_method' => env('SYMPLA_AUTH_METHOD'),
        'user_endpoint' => env('SYMPLA_OAUTH_USER_ENDPOINT'),
        'token_endpoint' => env('SYMPLA_OAUTH_TOKEN_ENDPOINT'),
        'client_id' => env('SYMPLA_OAUTH_CLIENT_ID'),
        'client_secret' => env('SYMPLA_OAUTH_CLIENT_SECRET'),
    ]
];
