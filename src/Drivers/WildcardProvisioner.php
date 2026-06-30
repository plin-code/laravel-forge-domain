<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Drivers;

use Carbon\CarbonImmutable;
use PlinCode\ForgeDomain\Contracts\DomainProvisioner;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Support\ReconcileReport;

final class WildcardProvisioner implements DomainProvisioner
{
    public function provision(ProvisionableDomain $domain): void
    {
        // Served by a pre-existing wildcard record and wildcard TLS at the infra
        // layer, so there is nothing to provision. Mark it active with a far
        // future expiry (the wildcard cert lifecycle is managed outside the app).
        $domain->markSslActive(CarbonImmutable::now()->addYears(10));
    }

    public function confirm(ProvisionableDomain $domain): bool
    {
        return true;
    }

    public function remove(ProvisionableDomain $domain): void
    {
        // No external resource to remove.
    }

    public function reconcile(iterable $domains): ReconcileReport
    {
        return new ReconcileReport(orphanedInForge: [], missingInForge: []);
    }
}
