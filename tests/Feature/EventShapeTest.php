<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Events\DomainActivated;
use PlinCode\LaravelForgeDomain\Events\DomainFailed;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;

it('carries the domain and reason', function (): void {
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    expect((new DomainActivated($domain))->domain)->toBe($domain)
        ->and((new DomainFailed($domain, 'boom'))->reason)->toBe('boom');
});
