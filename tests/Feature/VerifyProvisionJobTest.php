<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\DnsVerifierManager;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Events\DomainVerified;
use PlinCode\LaravelForgeDomain\Jobs\ConfirmSslJob;
use PlinCode\LaravelForgeDomain\Jobs\ProvisionDomainJob;
use PlinCode\LaravelForgeDomain\Jobs\VerifyDomainJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\FakeDnsResolver;

it('verifies then queues provisioning', function (): void {
    Bus::fake([ProvisionDomainJob::class]);
    Event::fake([DomainVerified::class]);

    $dns = new FakeDnsResolver;
    $dns->setCname('app.acme.com', ['custom.platform.test']);
    $this->app->instance(DnsResolver::class, $dns);
    config()->set('forge-domain.verification.cname_target', 'custom.platform.test');

    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    (new VerifyDomainJob($domain))->handle(new DnsVerifierManager($this->app, config('forge-domain')));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Verified);
    Event::assertDispatched(DomainVerified::class);
    Bus::assertDispatched(ProvisionDomainJob::class);
});

it('verifies subdomains without dns and queues provisioning', function (): void {
    Bus::fake([ProvisionDomainJob::class]);
    Event::fake([DomainVerified::class]);

    // No DNS resolver records set; a real lookup would return nothing for this hostname.
    $domain = ManagedDomain::create(['hostname' => 'acme.platform.test', 'kind' => DomainKind::Subdomain]);

    (new VerifyDomainJob($domain))->handle(new DnsVerifierManager($this->app, config('forge-domain')));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Verified);
    Event::assertDispatched(DomainVerified::class);
    Bus::assertDispatched(ProvisionDomainJob::class);
});

it('provisions then queues ssl confirmation', function (): void {
    Bus::fake([ConfirmSslJob::class]);
    $domain = ManagedDomain::create(['hostname' => 'acme.platform.test', 'kind' => DomainKind::Subdomain]);

    (new ProvisionDomainJob($domain))->handle(new DomainProvisioningManager($this->app, config('forge-domain')));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Active);
    Bus::assertDispatched(ConfirmSslJob::class);
});
