<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Drivers\ForgeProvisioner;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\FakeForge;
use Psr\Log\NullLogger;

function forgeDomain(): ManagedDomain
{
    return ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);
}

it('provisions a domain and certificate when managed', function (): void {
    $forge = new FakeForge;
    $driver = new ForgeProvisioner($forge, manage: true, sslDays: 90, logger: new NullLogger);
    $domain = forgeDomain();

    $driver->provision($domain);

    expect($domain->fresh()->getForgeDomainId())->toBe(1)
        ->and($forge->listDomainIds())->toBe([1]);

    expect($driver->confirm($domain))->toBeFalse();

    $forge->setActive(1, true);
    expect($driver->confirm($domain))->toBeTrue()
        ->and($domain->fresh()->getStatus())->toBe(DomainStatus::Active)
        ->and($forge->activated)->toHaveKey(1);
});

it('is a no-op when management is off', function (): void {
    $forge = new FakeForge;
    $driver = new ForgeProvisioner($forge, manage: false, sslDays: 90, logger: new NullLogger);
    $domain = forgeDomain();

    $driver->provision($domain);

    expect($forge->listDomainIds())->toBe([])
        ->and($driver->confirm($domain))->toBeTrue();
});

it('deletes on remove and diffs on reconcile', function (): void {
    $forge = new FakeForge;
    $driver = new ForgeProvisioner($forge, manage: true, sslDays: 90, logger: new NullLogger);
    $domain = forgeDomain();
    $driver->provision($domain);

    $orphanId = $forge->createDomain('orphan.test');
    $report = $driver->reconcile([$domain->fresh()]);

    expect($report->orphanedInForge)->toContain($orphanId)
        ->and($report->orphanedInForge)->not->toContain(1);

    $driver->remove($domain->fresh());
    expect($forge->listDomainIds())->toBe([$orphanId]);
});

it('does not delete on remove when management is off', function (): void {
    $forge = new FakeForge;
    $forgeId = $forge->createDomain('app.acme.com');
    $domain = forgeDomain();
    $domain->setForgeDomainId($forgeId);
    $driver = new ForgeProvisioner($forge, manage: false, sslDays: 90, logger: new NullLogger);

    $driver->remove($domain->fresh());

    expect($forge->listDomainIds())->toBe([$forgeId]);
});

it('reports missing forge ids on reconcile', function (): void {
    $forge = new FakeForge;
    $domain = forgeDomain();
    $domain->setForgeDomainId(99);
    $driver = new ForgeProvisioner($forge, manage: true, sslDays: 90, logger: new NullLogger);

    $report = $driver->reconcile([$domain->fresh()]);

    expect($report->missingInForge)->toContain(99)
        ->and($report->orphanedInForge)->toBeEmpty();
});
