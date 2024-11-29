<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('tw_random_string')) {
    function tw_random_string($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&?!#';
        $lengthCharacters = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, $lengthCharacters - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
if (!function_exists('tw_hash')) {
    function tw_hash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = tw_random_string(16);
        return $initializationVector . '_' . openssl_encrypt($value, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('tw_unhash')) {
    function tw_unhash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = strtok($value, '_');
        $value2 = substr($value, strpos($value, '_') + 1);
        return openssl_decrypt($value2, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('bcrypt')) {
    // because the bcrypt driver is directly called by the AppShell package :-(
    // https://konekt.dev/appshell/3.x/README
    function bcrypt($value, $options = [])
    {
        return tw_hash($value);
    }
}
if (!function_exists('format_subscription_price')) {
    function format_subscription_price($price, bool $taxIncluded = false, string $currency = null)
    {
        return sprintf(
                config('vanilo.foundation.currency.format'),
                $price,
                $currency ?? config('vanilo.foundation.currency.sign')
            ) . ' <span style="color:#ffaa00">/</span> month' . ($taxIncluded ? ' (incl. taxes)' : ' (excl. taxes)');
    }
}
if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2)
    {
        $units = array(' B', ' KiB', ' MiB', ' GiB', ' TiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . $units[$pow];
    }
}
if (!function_exists('app_url')) {
    function app_url(): string
    {
        return rtrim(config('app.url'), '/');
    }
}
if (!function_exists('is_cywise')) {
    function is_cywise(): bool
    {
        return mb_strtolower(config('app.name')) === 'cywise';
    }
}
if (!function_exists('app_header')) {
    function app_header(): array
    {
        if (Auth::check()) {
            return [
                [
                    'label' => __('Logout'),
                    'route' => route('logout'),
                    'post_form' => true,
                ]
            ];
        }
        return [
            [
                'label' => __('Login'),
                'route' => route('login'),
                'active' => request()->route()->named('login'),
            ], [
                'label' => __('Register'),
                'route' => route('register'),
                'active' => request()->route()->named('register'),
            ]
        ];
    }
}
if (!function_exists('app_menu')) {
    function app_menu(): array
    {
        if (Auth::check()) {
            return [
                [
                    'label' => __('Terms'),
                    'route' => route('terms'),
                ], [
                    'label' => __('Logout'),
                    'route' => route('logout'),
                    'post_form' => true,
                ]
            ];
        }
        return [
            [
                'label' => __('Login'),
                'route' => route('login'),
            ], [
                'label' => __('Register'),
                'route' => route('register'),
            ]
        ];
    }
}
if (!function_exists('app_sidebar')) {
    function app_sidebar(): array
    {
        if (Auth::check()) {
            return [
                [
                    'section_name' => __('Home'),
                    'section_items' => [
                        [
                            'label' => __('Overview'),
                            'route' => route('home', ['tab' => 'overview']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'overview',
                        ], [
                            'label' => __('Reports & Alerts'),
                            'route' => config('towerify.reports.url'),
                            'target' => '_blank',
                        ]
                    ]
                ], [
                    'section_name' => __('Vulnerability Scanner'),
                    'section_items' => [
                        [
                            'label' => __('Assets'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('assets'),
                            'target' => '_blank',
                        ], [
                            'label' => __('Vulnerabilities'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('vulnerabilities'),
                            'target' => '_blank',
                        ], [
                            'label' => __('Service Provider Delegation'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('delegation'),
                            'target' => '_blank',
                        ],
                    ]
                ], [
                    'section_name' => __('Agents'),
                    'section_items' => [
                        [
                            'label' => __('Servers'),
                            'route' => route('home', ['tab' => 'servers', 'servers_type' => 'instrumented']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'servers' && request()->get('servers_type') === 'instrumented',
                        ], [
                            'label' => __('Security Rules'),
                            'route' => route('home', ['tab' => 'security_rules']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'security_rules',
                        ],
                    ]
                ], [
                    'section_name' => __('Honeypots'),
                    'section_items' => [
                        [
                            'label' => __('Honeypots'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('setup_honeypots'),
                            'target' => '_blank',
                        ], [
                            'label' => __('Attackers'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('attackers'),
                            'target' => '_blank',
                        ], [
                            'label' => __('IP Blacklist'),
                            'route' => App\Modules\AdversaryMeter\Helpers\AdversaryMeter::redirectUrl('blacklist'),
                            'target' => '_blank',
                        ],
                    ],
                ], [
                    'section_name' => __('ISSP'),
                    'hidden' => !Auth::user()->canUseCyberBuddy(),
                    'section_items' => [
                        [
                            'label' => __('AI Writer'),
                            'route' => route('home', ['tab' => 'ia_writer']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'ia_writer',
                        ], [
                            'label' => __('AMA'),
                            'route' => route('home', ['tab' => 'ama']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'ama',
                        ], [
                            'label' => __('Conversations'),
                            'route' => route('home', ['tab' => 'conversations']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'conversations',
                        ], [
                            'label' => __('Collections'),
                            'route' => route('home', ['tab' => 'collections']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'collections',
                        ], [
                            'label' => __('Documents'),
                            'route' => route('home', ['tab' => 'knowledge_base']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'knowledge_base',
                        ], [
                            'label' => __('Chunks'),
                            'route' => route('home', ['tab' => 'chunks']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'chunks',
                        ], [
                            'label' => __('Prompts'),
                            'route' => route('home', ['tab' => 'prompts']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'prompts',
                        ]
                    ],
                ], [
                    'section_name' => __('YunoHost'),
                    'section_items' => [
                        [
                            'label' => __('Desktop'),
                            'route' => route('home', ['tab' => 'my-apps']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'my-apps',
                        ], [
                            'label' => __('Servers'),
                            'route' => route('home', ['tab' => 'servers', 'servers_type' => 'ynh']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'servers' && request()->get('servers_type') === 'ynh',
                            'hidden' => !Auth::user()->canListServers()
                        ], [
                            'label' => __('Applications'),
                            'route' => route('home', ['tab' => 'applications']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'applications',
                            'hidden' => !Auth::user()->canListServers(),
                        ], [
                            'label' => __('Domains'),
                            'route' => route('home', ['tab' => 'domains']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'domains',
                            'hidden' => !Auth::user()->canListServers(),
                        ], [
                            'label' => __('Backups'),
                            'route' => route('home', ['tab' => 'backups']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'backups',
                            'hidden' => !Auth::user()->canListServers(),
                        ], [
                            'label' => __('Interdependencies'),
                            'route' => route('home', ['tab' => 'interdependencies']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'interdependencies',
                            'hidden' => !Auth::user()->canListServers(),
                        ], [
                            'label' => __('Traces'),
                            'route' => route('home', ['tab' => 'traces']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'traces',
                            'hidden' => !Auth::user()->canListServers(),
                        ]
                    ]
                ], [
                    'section_name' => __('Marketplace'),
                    'section_items' => [
                        [
                            'label' => __('Admin'),
                            'route' => config('konekt.app_shell.ui.url'),
                            'active' => request()->route()->named('admin'),
                            'hidden' => !Auth::user()->isAdmin(),
                        ], [
                            'label' => __('Products'),
                            'route' => route('product.index'),
                            'active' => request()->route()->named('product.index'),
                        ], [
                            'label' => __('Cart'),
                            'route' => route('cart.show'),
                            'active' => request()->route()->named('cart.show'),
                        ], [
                            'label' => __('Orders'),
                            'route' => route('home', ['tab' => 'orders']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'orders',
                        ]
                    ]
                ], [
                    'section_name' => __('Settings'),
                    'section_items' => [
                        [
                            'label' => __('Users'),
                            'route' => route('home', ['tab' => 'users']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'users',
                            'hidden' => !Auth::user()->canListUsers(),
                        ], [
                            'label' => __('Invitations'),
                            'route' => route('home', ['tab' => 'invitations']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'invitations',
                            'hidden' => !Auth::user()->canListUsers(),
                        ], [
                            'label' => __('Plans'),
                            'route' => route('plans'),
                        ], [
                            'label' => __('My Subscription'),
                            'route' => route('customer-portal'),
                        ], [
                            'label' => __('Documentation'),
                            'route' => 'https://computablefacts.notion.site/AdversaryMeter-a30527edc0554ea8aabf7cb7d0137258?pvs=4',
                            'target' => '_blank',
                        ], [
                            'label' => __('Terms'),
                            'route' => route('terms'),
                            'active' => request()->route()->named('terms'),
                        ], [
                            'label' => __('Reset Password'),
                            'route' => route('reset-password'),
                            'active' => request()->route()->named('reset-password'),
                            'post_form' => true,
                        ]
                    ]
                ]
            ];
        }
        return [
            //
        ];
    }
}
