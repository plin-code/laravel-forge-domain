<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PlinCode\ForgeDomain\Contracts\DnsResolver;
use PlinCode\ForgeDomain\DnsVerifierManager;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Events\DomainVerified;
use PlinCode\ForgeDomain\Jobs\ConfirmSslJob;
use PlinCode\ForgeDomain\Jobs\ProvisionDomainJob;
use PlinCode\ForgeDomain\Jobs\VerifyDomainJob;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\FakeDnsResolver;

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
