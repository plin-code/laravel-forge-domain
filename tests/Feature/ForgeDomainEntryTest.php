<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use PlinCode\LaravelForgeDomain\Facades\ForgeDomain;
use PlinCode\LaravelForgeDomain\Jobs\ReconcileDomainsJob;
use PlinCode\LaravelForgeDomain\Jobs\RenewSslJob;
use PlinCode\LaravelForgeDomain\Jobs\VerifyDomainJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;

it('onboard dispatches verification', function (): void {
    Bus::fake([VerifyDomainJob::class]);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    ForgeDomain::onboard($domain);

    Bus::assertDispatched(VerifyDomainJob::class);
});

it('renew command dispatches a renew job for near-expiry domains', function (): void {
    Bus::fake([RenewSslJob::class]);
    ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
        'ssl_expires_at' => now()->addDays(3),
    ]);

    $this->artisan('forge-domain:renew-ssl')->assertSuccessful();
    Bus::assertDispatched(RenewSslJob::class);
});

it('reconcile command dispatches the reconcile job', function (): void {
    Bus::fake([ReconcileDomainsJob::class]);

    $this->artisan('forge-domain:reconcile')->assertSuccessful();
    Bus::assertDispatched(ReconcileDomainsJob::class);
});
