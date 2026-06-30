<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Contracts;

use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\VerificationMethod;

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
