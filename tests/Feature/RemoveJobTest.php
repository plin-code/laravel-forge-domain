<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Events\DomainRemoved;
use PlinCode\ForgeDomain\Jobs\RemoveDomainJob;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\FakeForge;

it('removes the forge domain and marks removed', function (): void {
    Event::fake([DomainRemoved::class]);
    config()->set('forge-domain.manage', true);
    $forge = new FakeForge;
    $this->app->instance(ForgeClient::class, $forge);

    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);
    $domain->setForgeDomainId($forge->createDomain('app.acme.com'));

    (new RemoveDomainJob($domain))->handle(new DomainProvisioningManager($this->app, config('forge-domain')));

    expect($forge->listDomainIds())->toBe([])
        ->and($domain->fresh()->getStatus())->toBe(DomainStatus::Removed);
    Event::assertDispatched(DomainRemoved::class);
});
