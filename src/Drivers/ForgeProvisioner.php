<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Drivers;

use Carbon\CarbonImmutable;
use PlinCode\ForgeDomain\Contracts\DomainProvisioner;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Support\ReconcileReport;
use Psr\Log\LoggerInterface;

final class ForgeProvisioner implements DomainProvisioner
{
    public function __construct(
        private ForgeClient $forge,
        private bool $manage,
        private int $sslDays,
        private LoggerInterface $logger,
    ) {}

    public function provision(ProvisionableDomain $domain): void
    {
        if (! $this->manage) {
            $this->logger->info('forge-domain management disabled; skipping provision', [
                'hostname' => $domain->getHostname(),
            ]);

            return;
        }

        $forgeDomainId = $this->forge->createDomain($domain->getHostname(), false);
        $domain->setForgeDomainId($forgeDomainId);
        $this->forge->createCertificate($forgeDomainId);
    }

    public function confirm(ProvisionableDomain $domain): bool
    {
        if (! $this->manage) {
            $domain->markSslActive(CarbonImmutable::now()->addDays($this->sslDays));

            return true;
        }

        $forgeDomainId = $domain->getForgeDomainId();

        if ($forgeDomainId === null || ! $this->forge->certificateIsActive($forgeDomainId)) {
            return false;
        }

        $this->forge->activateCertificate($forgeDomainId);
        $domain->markSslActive(CarbonImmutable::now()->addDays($this->sslDays));

        return true;
    }

    public function remove(ProvisionableDomain $domain): void
    {
        $forgeDomainId = $domain->getForgeDomainId();

        if ($this->manage && $forgeDomainId !== null) {
            $this->forge->deleteDomain($forgeDomainId);
        }
    }

    public function reconcile(iterable $domains): ReconcileReport
    {
        $forgeIds = $this->forge->listDomainIds();

        $dbIds = [];
        foreach ($domains as $domain) {
            $id = $domain->getForgeDomainId();
            if ($id !== null) {
                $dbIds[] = $id;
            }
        }

        return new ReconcileReport(
            orphanedInForge: array_values(array_diff($forgeIds, $dbIds)),
            missingInForge: array_values(array_diff($dbIds, $forgeIds)),
        );
    }
}
