<?php

return [
    'hasher' => [
        'nonce' => env('HASHER_NONCE'),
    ],
    'adversarymeter' => [
        'url' => env('AM_URL'),
        'api_key' => env('AM_API_KEY'),
        'ip_addresses' => explode(',', env('AM_IP_ADDRESSES')),
    ],
    'telescope' => [
        'whitelist' => [
            'usernames' => explode(',', env('TELESCOPE_WHITELIST_USERNAMES')),
            'domains' => explode(',', env('TELESCOPE_WHITELIST_DOMAINS')),
        ],
    ],
    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'username' => env('ADMIN_USERNAME'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
