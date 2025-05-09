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
                /* [
                    'section_name' => __('Home'),
                    'hidden' => !Auth::user()->canViewHome(),
                    'section_items' => [
                        [
                            'label' => __('Overview'),
                            'route' => route('home', ['tab' => 'overview']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'overview',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_OVERVIEW),
                        ], [
                            'label' => __('Metrics'),
                            'route' => 'https://' . Auth::user()->performa_domain,
                            'target' => '_blank',
                            'hidden' => is_null(Auth::user()->performa_domain) || !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_METRICS),
                        ], [
                            'label' => __('Events'),
                            'route' => config('towerify.reports.url'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_EVENTS),
                        ]
                    ]
                ], [
                    'section_name' => __('Vulnerability Scanner'),
                    'hidden' => !Auth::user()->canViewVulnerabilityScanner(),
                    'section_items' => [
                        [
                            'label' => __('Assets'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('assets'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_ASSETS),
                        ], [
                            'label' => __('Vulnerabilities'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('vulnerabilities'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_VULNERABILITIES),
                        ], [
                            'label' => __('Service Provider Delegation'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('delegation'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_SERVICE_PROVIDER_DELEGATION),
                        ],
                    ]
                ], */ [
                    'section_name' => __('Agents'),
                    'hidden' => !Auth::user()->canViewAgents(),
                    'section_items' => [
                        [
                            'label' => __('Servers'),
                            'route' => route('home', ['tab' => 'servers', 'servers_type' => 'instrumented']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'servers' && request()->get('servers_type') === 'instrumented',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_AGENTS),
                        ], [
                            'label' => __('Security Rules'),
                            'route' => route('home', ['tab' => 'security_rules']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'security_rules',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_SECURITY_RULES),
                        ],
                    ]
                ], /* [
                    'section_name' => __('Honeypots'),
                    'hidden' => !Auth::user()->canViewHoneypots(),
                    'section_items' => [
                        [
                            'label' => __('Honeypots'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('setup_honeypots'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_HONEYPOTS),
                        ], [
                            'label' => __('Attackers'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('attackers'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_ATTACKERS),
                        ], [
                            'label' => __('IP Blacklist'),
                            'route' => \App\Helpers\AdversaryMeter::redirectUrl('blacklist'),
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_IP_BLACKLIST),
                        ],
                    ],
                ], */ [
                    'section_name' => __('ISSP'),
                    'hidden' => !Auth::user()->canViewIssp(),
                    'section_items' => [
                        [
                            'label' => __('Hardening'),
                            'route' => route('home', ['tab' => 'sca']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'sca',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_HARDENING),
                        ], [
                            'label' => __('Frameworks'),
                            'route' => route('home', ['tab' => 'frameworks']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'frameworks',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_FRAMEWORKS),
                        ], [
                            'label' => __('AI Writer'),
                            'route' => route('home', ['tab' => 'ai_writer']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'ai_writer',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_AI_WRITER),
                        ], [
                            'label' => __('CyberBuddy'),
                            'route' => route('home', ['tab' => 'ama']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'ama',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_CYBERBUDDY),
                        ], [
                            'label' => __('CyberBuddy (nextgen)'),
                            'route' => route('home', ['tab' => 'ama2']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'ama2',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_CYBERBUDDY),
                        ], [
                            'label' => __('Conversations'),
                            'route' => route('home', ['tab' => 'conversations']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'conversations',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_CONVERSATIONS),
                        ], [
                            'label' => __('Collections'),
                            'route' => route('home', ['tab' => 'collections']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'collections',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_COLLECTIONS),
                        ], [
                            'label' => __('Documents'),
                            'route' => route('home', ['tab' => 'documents']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'documents',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_DOCUMENTS),
                        ], [
                            'label' => __('Tables'),
                            'route' => route('home', ['tab' => 'tables']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'tables',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_TABLES),
                        ], [
                            'label' => __('Chunks'),
                            'route' => route('home', ['tab' => 'chunks']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'chunks',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_CHUNKS),
                        ], [
                            'label' => __('Prompts'),
                            'route' => route('home', ['tab' => 'prompts']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'prompts',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_PROMPTS),
                        ]
                    ],
                ], [
                    'section_name' => __('YunoHost'),
                    'hidden' => !Auth::user()->canViewYunoHost(),
                    'section_items' => [
                        [
                            'label' => __('Desktop'),
                            'route' => route('home', ['tab' => 'my-apps']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'my-apps',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_DESKTOP),
                        ], [
                            'label' => __('Servers'),
                            'route' => route('home', ['tab' => 'servers', 'servers_type' => 'ynh']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'servers' && request()->get('servers_type') === 'ynh',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_SERVERS),
                        ], [
                            'label' => __('Applications'),
                            'route' => route('home', ['tab' => 'applications']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'applications',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_APPLICATIONS),
                        ], [
                            'label' => __('Domains'),
                            'route' => route('home', ['tab' => 'domains']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'domains',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_DOMAINS),
                        ], [
                            'label' => __('Backups'),
                            'route' => route('home', ['tab' => 'backups']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'backups',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_BACKUPS),
                        ], [
                            'label' => __('Interdependencies'),
                            'route' => route('home', ['tab' => 'interdependencies']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'interdependencies',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_INTERDEPENDENCIES),
                        ], [
                            'label' => __('Traces'),
                            'route' => route('home', ['tab' => 'traces']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'traces',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_TRACES),
                        ]
                    ]
                ], [
                    'section_name' => __('Marketplace'),
                    'hidden' => !Auth::user()->canViewMarketplace(),
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
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_PRODUCTS),
                        ], [
                            'label' => __('Cart'),
                            'route' => route('cart.show'),
                            'active' => request()->route()->named('cart.show'),
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_CART),
                        ], [
                            'label' => __('Orders'),
                            'route' => route('home', ['tab' => 'orders']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'orders',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_ORDERS),
                        ]
                    ]
                ], [
                    'section_name' => __('Settings'),
                    'hidden' => !Auth::user()->canViewSettings(),
                    'section_items' => [
                        [
                            'label' => __('Users'),
                            'route' => route('home', ['tab' => 'users']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'users',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_USERS),
                        ], [
                            'label' => __('Invitations'),
                            'route' => route('home', ['tab' => 'invitations']),
                            'active' => request()->route()->named('home') && request()->get('tab') === 'invitations',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_INVITATIONS),
                        ], [
                            'label' => __('Plans'),
                            'route' => route('plans'),
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_PLANS),
                        ], [
                            'label' => __('My Subscription'),
                            'route' => route('customer-portal'),
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_MY_SUBSCRIPTION),
                        ], [
                            'label' => __('Documentation'),
                            'route' => 'https://computablefacts.notion.site/AdversaryMeter-a30527edc0554ea8aabf7cb7d0137258?pvs=4',
                            'target' => '_blank',
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_DOCUMENTATION),
                        ], [
                            'label' => __('Terms'),
                            'route' => route('terms'),
                            'active' => request()->route()->named('terms'),
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_TERMS),
                        ], [
                            'label' => __('Reset Password'),
                            'route' => route('reset-password'),
                            'active' => request()->route()->named('reset-password'),
                            'hidden' => !Auth::user()->hasPermissionTo(\App\Models\Permission::VIEW_RESET_PASSWORD),
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
