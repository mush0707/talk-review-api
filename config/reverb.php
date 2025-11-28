<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    */
    'servers' => [
        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => (int) env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST', 'localhost'),

            'options' => [
                'tls' => [],
            ],

            'max_request_size' => (int) env('REVERB_MAX_REQUEST_SIZE', 10_000),

            'scaling' => [
                'enabled' => (bool) env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'redis' => [
                    'connection' => env('REVERB_REDIS_CONNECTION', 'default'),
                ],
            ],

            'pulse_ingest_interval' => (int) env('REVERB_PULSE_INGEST_INTERVAL', 15),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Applications
    |--------------------------------------------------------------------------
    */
    'apps' => [

        // This must be "config" for local projects
        'provider' => 'config',

        'apps' => [
            [
                'key' => env('REVERB_APP_KEY', 'local'),
                'secret' => env('REVERB_APP_SECRET', 'local'),
                'app_id' => env('REVERB_APP_ID', 'local'),
                'path' => env('REVERB_PATH', ''),
                'allowed_origins' => ['*'], // todo change this in future
                'ping_interval' => (int) env('REVERB_APP_PING_INTERVAL', 60),
                'activity_timeout' => (int) env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                'max_connections' => env('REVERB_APP_MAX_CONNECTIONS', null),
                'max_message_size' => (int) env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),

                'options' => [
                    'host' => env('REVERB_HOST', '127.0.0.1'),
                    'port' => (int) env('REVERB_PORT', 443),

                    // for local ws:// use http
                    'scheme' => env('REVERB_SCHEME', 'http'),
                    'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
                ],
            ],
        ],
    ],
];
