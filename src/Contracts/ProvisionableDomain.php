<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Contracts;

use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\VerificationMethod;

interface ProvisionableDomain
{
    public function getKey(): mixed;

    public function getHostname(): string;

    public function getKind(): DomainKind;

    public function getVerificationMethod(): ?VerificationMethod;

    public function getVerificationToken(): ?string;

    public function getDnsTarget(): ?string;

    public function getForgeDomainId(): ?int;

    public function setForgeDomainId(?int $id): void;

    public function getStatus(): DomainStatus;

    public function markVerified(): void;

    public function markProvisioning(): void;

    public function markSslActive(\DateTimeInterface $expiresAt): void;

    public function markFailed(string $reason): void;

    public function markRemoved(): void;
}
