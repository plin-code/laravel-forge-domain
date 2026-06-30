<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use PlinCode\LaravelForgeDomain\Events\DomainFailed;
use PlinCode\LaravelForgeDomain\Jobs\ConfirmSslJob;
use PlinCode\LaravelForgeDomain\Jobs\ProvisionDomainJob;
use PlinCode\LaravelForgeDomain\Jobs\RenewSslJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;

it('provision job marks domain failed and emits event on terminal failure', function (): void {
    Event::fake([DomainFailed::class]);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    (new ProvisionDomainJob($domain))->failed(new RuntimeException('boom'));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Failed);
    Event::assertDispatched(DomainFailed::class, fn (DomainFailed $e): bool => $e->reason === 'boom');
});

it('confirm ssl job marks domain failed and emits event on terminal failure', function (): void {
    Event::fake([DomainFailed::class]);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    (new ConfirmSslJob($domain))->failed(new RuntimeException('boom'));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Failed);
    Event::assertDispatched(DomainFailed::class, fn (DomainFailed $e): bool => $e->reason === 'boom');
});

it('renew ssl job marks domain failed and emits event on terminal failure', function (): void {
    Event::fake([DomainFailed::class]);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    (new RenewSslJob($domain))->failed(new RuntimeException('boom'));

    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Failed);
    Event::assertDispatched(DomainFailed::class, fn (DomainFailed $e): bool => $e->reason === 'boom');
});
