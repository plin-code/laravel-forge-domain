<?php

declare(strict_types=1);

use PlinCode\ForgeDomain\Contracts\DnsResolver;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DnsVerifierManager;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\ForgeDomain;
use PlinCode\ForgeDomain\Support\ForgeSdkClient;
use PlinCode\ForgeDomain\Support\PhpDnsResolver;

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
