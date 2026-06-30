<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;

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
        // log | cleanup
        // WARNING: cleanup deletes every Forge domain on the configured site that
        // the package does not track. Only enable this when the Forge site is
        // dedicated exclusively to package-managed domains. Any manually created
        // domain on that site will be permanently deleted without further warning.
        'mode' => 'log',
    ],

    'models' => [
        'managed_domain' => ManagedDomain::class,
    ],
];
