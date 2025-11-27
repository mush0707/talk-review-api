<?php declare(strict_types=1);

return [
    'default' => env('ELASTIC_CONNECTION', 'default'),
    'connections' => [
        'default' => [
            'hosts' => [
                env('ELASTIC_HOST', 'elasticsearch:9200'),
            ],
            'basicAuthentication' => [
                env('ELASTIC_USERNAME', 'elastic'),
                env('ELASTIC_PASSWORD', 'MagicWor'),
            ],
            'SSLVerification' => false
        ],
    ],
];
