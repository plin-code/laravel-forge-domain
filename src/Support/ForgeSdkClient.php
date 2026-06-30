<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Support;

use Laravel\Forge\Forge;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use Throwable;

final class ForgeSdkClient implements ForgeClient
{
    // VERIFY-LIVE: the v4 certificate-action verb. plincode used 'enable'; v3's
    // helper was activate() and the resource flag is Certificate.active, so
    // 'activate' is the likely-correct value. Confirm against the live Forge API
    // (the integration test in Task 17 settles this). Change only this constant.
    private const ACTIVATE_VERB = 'activate';

    public function __construct(
        private Forge $forge,
        private string $organization,
        private int $serverId,
        private int $siteId,
    ) {}

    public function createDomain(string $hostname, bool $allowWildcard = false): int
    {
        $domain = $this->forge->createDomain($this->organization, $this->serverId, $this->siteId, [
            'name' => $hostname,
            'allow_wildcard_subdomains' => $allowWildcard,
            'www_redirect_type' => 'none',
        ]);

        return (int) $domain->id;
    }

    public function createCertificate(int $forgeDomainId): void
    {
        $this->forge->createCertificate($this->organization, $this->serverId, $this->siteId, $forgeDomainId, [
            'type' => 'letsencrypt',
            'letsencrypt' => ['verification_method' => 'http-01', 'key_type' => 'ecdsa'],
        ]);
    }

    public function certificateIsActive(int $forgeDomainId): bool
    {
        try {
            $certificate = $this->forge->activeDomainCertificate(
                $this->organization, $this->serverId, $this->siteId, $forgeDomainId,
            );

            return (bool) ($certificate->active ?? false);
        } catch (Throwable) {
            return false;
        }
    }

    public function activateCertificate(int $forgeDomainId): void
    {
        $certificate = $this->forge->activeDomainCertificate(
            $this->organization, $this->serverId, $this->siteId, $forgeDomainId,
        );

        $this->forge->createCertificateAction(
            $this->organization, $this->serverId, $this->siteId, $forgeDomainId, (int) $certificate->id,
            ['action' => self::ACTIVATE_VERB],
        );
    }

    public function deleteDomain(int $forgeDomainId): void
    {
        $this->forge->deleteDomain($this->organization, $this->serverId, $this->siteId, $forgeDomainId);
    }

    public function listDomainIds(): array
    {
        $ids = [];
        $page = 1;

        do {
            $paginator = $this->forge->domains($this->organization, $this->serverId, $this->siteId, ['page' => $page]);
            foreach ($paginator->items() as $domain) {
                $ids[] = (int) $domain->id;
            }
            $page++;
        } while ($paginator->hasMorePages());

        return $ids;
    }
}
