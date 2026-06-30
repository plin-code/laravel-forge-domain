<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Events\DomainActivated;
use PlinCode\ForgeDomain\Jobs\ConfirmSslJob;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\FakeForge;

it('emits activated when the certificate is active', function (): void {
    Event::fake([DomainActivated::class]);
    config()->set('forge-domain.manage', true);
    $forge = new FakeForge;
    $this->app->instance(ForgeClient::class, $forge);

    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);
    $domain->setForgeDomainId($forge->createDomain('app.acme.com'));
    $forge->setActive($domain->getForgeDomainId(), true);

    (new ConfirmSslJob($domain))->handle(new DomainProvisioningManager($this->app, config('forge-domain')));

    Event::assertDispatched(DomainActivated::class);
});
