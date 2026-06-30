<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Concerns;

use DateTimeInterface;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\VerificationMethod;

trait HasProvisionableDomain
{
    public function getHostname(): string
    {
        return (string) $this->hostname;
    }

    public function getKind(): DomainKind
    {
        return $this->kind;
    }

    public function getVerificationMethod(): ?VerificationMethod
    {
        return $this->verification_method;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verification_token;
    }

    public function getDnsTarget(): ?string
    {
        return $this->dns_target;
    }

    public function getForgeDomainId(): ?int
    {
        return $this->forge_domain_id === null ? null : (int) $this->forge_domain_id;
    }

    public function setForgeDomainId(?int $id): void
    {
        $this->forceFill(['forge_domain_id' => $id])->save();
    }

    public function getStatus(): DomainStatus
    {
        return $this->status;
    }

    public function markVerified(): void
    {
        $this->forceFill(['status' => DomainStatus::Verified, 'failure_reason' => null])->save();
    }

    public function markProvisioning(): void
    {
        $this->forceFill(['status' => DomainStatus::Provisioning])->save();
    }

    public function markSslActive(DateTimeInterface $expiresAt): void
    {
        $this->forceFill(['status' => DomainStatus::Active, 'ssl_expires_at' => $expiresAt])->save();
    }

    public function markFailed(string $reason): void
    {
        $this->forceFill(['status' => DomainStatus::Failed, 'failure_reason' => $reason])->save();
    }

    public function markRemoved(): void
    {
        $this->forceFill(['status' => DomainStatus::Removed])->save();
    }
}
