<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Drivers\ForgeProvisioner;
use PlinCode\ForgeDomain\Drivers\WildcardProvisioner;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\FakeForge;

beforeEach(function (): void {
    $this->app->instance(ForgeClient::class, new FakeForge);
});

it('resolves the forge driver for custom domains', function (): void {
    $manager = new DomainProvisioningManager($this->app, config('forge-domain'));
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    expect($manager->for($domain))->toBeInstanceOf(ForgeProvisioner::class);
});

it('resolves the wildcard driver for subdomains and marks active', function (): void {
    $manager = new DomainProvisioningManager($this->app, config('forge-domain'));
    $domain = ManagedDomain::create(['hostname' => 'acme.platform.test', 'kind' => DomainKind::Subdomain]);

    $driver = $manager->for($domain);
    expect($driver)->toBeInstanceOf(WildcardProvisioner::class);

    $driver->provision($domain);
    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Active)
        ->and($driver->confirm($domain))->toBeTrue();
});

it('throws on an unknown driver', function (): void {
    $manager = new DomainProvisioningManager($this->app, config('forge-domain'));

    $manager->driver('nope');
})->throws(InvalidArgumentException::class);
