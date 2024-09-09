<?php

return [
    'hasher' => [
        'nonce' => env('HASHER_NONCE'),
    ],
    'adversarymeter' => [
        'url' => env('AM_URL'),
        'api_key' => env('AM_API_KEY'),
        'ip_addresses' => explode(',', env('AM_IP_ADDRESSES')),
        'api' => env('AM_API'),
        'api_username' => env('AM_API_USERNAME'),
        'api_password' => env('AM_API_PASSWORD'),
        'drop_scan_events_after_x_minutes' => env('DROP_SCAN_EVENTS_AFTER_X_MINUTES', 3 * 24 * 60),
        'drop_discovery_events_after_x_minutes' => env('DROP_DISCOVERY_EVENTS_AFTER_X_MINUTES', 1 * 24 * 60),
        'days_between_scans' => env('DAYS_BETWEEN_SCANS', 5),
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
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],
    'scraperapi' => [
        'api_key' => env('SCRAPERAPI_API_KEY'),
    ],
];
