<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\DnsVerifierManager;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\ForgeDomain;
use PlinCode\LaravelForgeDomain\Support\ForgeSdkClient;
use PlinCode\LaravelForgeDomain\Support\PhpDnsResolver;

it('binds the package services', function (): void {
    config()->set('forge-domain.forge.organization', 'acme');
    config()->set('forge-domain.forge.server_id', 1);
    config()->set('forge-domain.forge.site_id', 2);
    config()->set('forge-domain.forge.token', 'tok');

    expect(app(DnsResolver::class))->toBeInstanceOf(PhpDnsResolver::class)
        ->and(app(ForgeClient::class))->toBeInstanceOf(ForgeSdkClient::class)
        ->and(app(DomainProvisioningManager::class))->toBeInstanceOf(DomainProvisioningManager::class)
        ->and(app(DnsVerifierManager::class))->toBeInstanceOf(DnsVerifierManager::class)
        ->and(app('forge-domain'))->toBeInstanceOf(ForgeDomain::class);
});
