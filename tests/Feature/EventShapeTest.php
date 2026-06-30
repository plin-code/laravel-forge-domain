<?php

declare(strict_types=1);

use PlinCode\ForgeDomain\Events\DomainActivated;
use PlinCode\ForgeDomain\Events\DomainFailed;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;

it('carries the domain and reason', function (): void {
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    expect((new DomainActivated($domain))->domain)->toBe($domain)
        ->and((new DomainFailed($domain, 'boom'))->reason)->toBe('boom');
});
