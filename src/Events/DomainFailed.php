<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Events;

use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;

final class DomainFailed
{
    public function __construct(
        public ProvisionableDomain $domain,
        public string $reason,
    ) {}
}
