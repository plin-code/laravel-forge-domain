<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Contracts;

use PlinCode\ForgeDomain\Support\ReconcileReport;

interface DomainProvisioner
{
    public function provision(ProvisionableDomain $domain): void;

    public function confirm(ProvisionableDomain $domain): bool;

    public function remove(ProvisionableDomain $domain): void;

    /** @param iterable<ProvisionableDomain> $domains */
    public function reconcile(iterable $domains): ReconcileReport;
}
