<?php

return [
    'oauth' => [
        'auth_method' => env('SYMPLA_AUTH_METHOD') ?: 'header',
        'user_endpoint' => env('SYMPLA_SSO_BASE_URL').'tokeninfo',
        'token_endpoint' => env('SYMPLA_OAUTH_TOKEN_ENDPOINT') ?: '',
        'client_id' => env('SYMPLA_OAUTH_CLIENT_ID') ?: '',
        'client_secret' => env('SYMPLA_OAUTH_CLIENT_SECRET') ?: '',
        'client_provider' => env('SYMPLA_AUTH_CLIENT_PROVIDER') ?: '',
        'client_domain' => env('SYMPLA_AUTH_DOMAIN') ?: '',
        'client_service_account' => env('SYMPLA_AUTH_SERVICE_ACCOUNT') ?: '',
        'public_key_path' => env('SYMPLA_AUTH_PUBLIC_KEY') ?: ''
    ]
];
