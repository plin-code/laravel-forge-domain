<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;

it('mutates and persists status through the contract', function (): void {
    $domain = ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
        'status' => DomainStatus::Pending,
    ]);

    expect($domain->getKind())->toBe(DomainKind::Custom)
        ->and($domain->getStatus())->toBe(DomainStatus::Pending);

    $domain->markVerified();
    $domain->setForgeDomainId(42);
    $domain->markSslActive(now()->addDays(90));

    $fresh = $domain->fresh();
    expect($fresh->getStatus())->toBe(DomainStatus::Active)
        ->and($fresh->getForgeDomainId())->toBe(42)
        ->and($fresh->ssl_expires_at)->not->toBeNull();

    $domain->markFailed('dns mismatch');
    expect($domain->fresh()->getStatus())->toBe(DomainStatus::Failed);
});
