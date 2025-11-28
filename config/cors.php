<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'broadcasting/auth',
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ],

    'allowed_methods' => ['*'],

    // IMPORTANT: If you ever use cookies/credentials, don't use '*'
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],

    'allowed_origins_patterns' => [],

    // Allow Authorization header (Bearer token) and everything else
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // OK for both token and cookie auth.
    // If you will NEVER use cookies, you can set this to false.
    'supports_credentials' => true,
];
