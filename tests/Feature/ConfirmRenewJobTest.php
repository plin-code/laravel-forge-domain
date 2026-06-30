<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Events\DomainActivated;
use PlinCode\LaravelForgeDomain\Jobs\ConfirmSslJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\FakeForge;

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
