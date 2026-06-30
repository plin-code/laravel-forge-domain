<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Events;

use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;

final class DomainRemoved
{
    public function __construct(public ProvisionableDomain $domain) {}
}
