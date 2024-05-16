<?php

return [
    'modules' => [
        Konekt\AppShell\Providers\ModuleServiceProvider::class => [
            'ui' => [
                'name' => 'Towerify',
                'url' => '/admin/product',
            ]
        ],
        Vanilo\Foundation\Providers\ModuleServiceProvider::class => [
            'currency' => [
                'code' => 'EUR',
                'sign' => '€',
                'format' => '%1$g%2$s',
            ],
            'image' => [
                'taxon' => [
                    'variants' => [
                        'thumbnail' => [
                            'width' => 250,
                            'height' => 188,
                            'fit' => 'contain'
                        ],
                        'header' => [
                            'width' => 1110,
                            'height' => 150,
                            'fit' => 'contain'
                        ],
                        'card' => [
                            'width' => 521,
                            'height' => 293,
                            'fit' => 'contain'
                        ]
                    ]
                ],
                'variants' => [
                    'thumbnail' => [
                        'width' => 250,
                        'height' => 188,
                        'fit' => 'fill'
                    ],
                    'medium' => [
                        'width' => 540,
                        'height' => 406,
                        'fit' => 'fill'
                    ]
                ]
            ],
        ],
        Vanilo\Admin\Providers\ModuleServiceProvider::class,
        // Vanilo\Adyen\Providers\ModuleServiceProvider::class,
        // Vanilo\Braintree\Providers\ModuleServiceProvider::class,
        Vanilo\Mollie\Providers\ModuleServiceProvider::class,
        Vanilo\Euplatesc\Providers\ModuleServiceProvider::class,
        Vanilo\Netopia\Providers\ModuleServiceProvider::class,
        // Vanilo\Paypal\Providers\ModuleServiceProvider::class,
    ]
];
