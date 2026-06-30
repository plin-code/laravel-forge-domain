<?php

declare(strict_types=1);

use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;

return [
    'drivers' => [
        DomainKind::Custom->value => 'forge',
        DomainKind::Subdomain->value => 'wildcard',
    ],

    // Master kill-switch. When false the forge driver is a no-op that only logs,
    // so the package can be installed before Forge credentials exist.
    'manage' => env('FORGE_DOMAIN_MANAGE', false),

    'forge' => [
        'token' => env('FORGE_DOMAIN_TOKEN'),
        'organization' => env('FORGE_DOMAIN_ORGANIZATION'),
        'server_id' => env('FORGE_DOMAIN_SERVER_ID'),
        'site_id' => env('FORGE_DOMAIN_SITE_ID'),
        'server_ip' => env('FORGE_DOMAIN_SERVER_IP'),
    ],

    'verification' => [
        'method' => env('FORGE_DOMAIN_VERIFICATION', 'cname'),
        'txt_prefix' => '_forge-verify',
        'cname_target' => env('FORGE_DOMAIN_CNAME_TARGET'),
    ],

    'ssl' => [
        'active_days' => 90,
        'renew_days_before' => 14,
        'poll_tries' => 15,
        'poll_backoff' => 30,
    ],

    'reconcile' => [
        'mode' => 'log', // log | cleanup
    ],

    'models' => [
        'managed_domain' => ManagedDomain::class,
    ],
];
