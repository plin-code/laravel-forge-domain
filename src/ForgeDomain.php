<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain;

use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\Jobs\RemoveDomainJob;
use PlinCode\LaravelForgeDomain\Jobs\VerifyDomainJob;

final readonly class ForgeDomain
{
    public function __construct(
        private DomainProvisioningManager $provisioners,
    ) {}

    public function onboard(ProvisionableDomain $domain): void
    {
        VerifyDomainJob::dispatch($domain);
    }

    public function provision(ProvisionableDomain $domain): void
    {
        $this->provisioners->for($domain)->provision($domain);
    }

    public function remove(ProvisionableDomain $domain): void
    {
        RemoveDomainJob::dispatch($domain);
    }
}
