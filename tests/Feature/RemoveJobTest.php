<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Events\DomainRemoved;
use PlinCode\LaravelForgeDomain\Jobs\RemoveDomainJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\FakeForge;

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
