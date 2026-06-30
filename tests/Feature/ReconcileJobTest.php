<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Jobs\ReconcileDomainsJob;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\FakeForge;

it('logs orphaned forge domains in log mode', function (): void {
    config()->set('forge-domain.manage', true);
    config()->set('forge-domain.reconcile.mode', 'log');
    $forge = new FakeForge;
    $this->app->instance(ForgeClient::class, $forge);
    Log::spy();

    $known = $forge->createDomain('app.acme.com');
    ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
        'forge_domain_id' => $known,
    ]);
    $forge->createDomain('orphan.test'); // orphan, not in DB

    (new ReconcileDomainsJob)->handle(new DomainProvisioningManager($this->app, config('forge-domain')));

    Log::shouldHaveReceived('warning')->once();
});

it('deletes orphaned forge domains in cleanup mode', function (): void {
    config()->set('forge-domain.manage', true);
    config()->set('forge-domain.reconcile.mode', 'cleanup');
    $forge = new FakeForge;
    $this->app->instance(ForgeClient::class, $forge);

    $knownId = $forge->createDomain('app.acme.com');
    ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
        'forge_domain_id' => $knownId,
    ]);
    $orphanId = $forge->createDomain('orphan.test');

    (new ReconcileDomainsJob)->handle(new DomainProvisioningManager($this->app, config('forge-domain')));

    expect($forge->listDomainIds())->toContain($knownId)->not->toContain($orphanId);
});
