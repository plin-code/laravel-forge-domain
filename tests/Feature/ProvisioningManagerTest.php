<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Drivers\ForgeProvisioner;
use PlinCode\LaravelForgeDomain\Drivers\WildcardProvisioner;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\FakeForge;

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

it('throws when the kind has no mapped driver', function (): void {
    $config = config('forge-domain');
    $config['drivers'] = [];
    $manager = new DomainProvisioningManager($this->app, $config);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    $manager->for($domain);
})->throws(InvalidArgumentException::class);
