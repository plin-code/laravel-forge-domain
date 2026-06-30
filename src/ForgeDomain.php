<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain;

use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Jobs\RemoveDomainJob;
use PlinCode\ForgeDomain\Jobs\VerifyDomainJob;

final class ForgeDomain
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
