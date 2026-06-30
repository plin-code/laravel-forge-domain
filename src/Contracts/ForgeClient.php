<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Contracts;

interface ForgeClient
{
    public function createDomain(string $hostname, bool $allowWildcard = false): int;

    public function createCertificate(int $forgeDomainId): void;

    public function certificateIsActive(int $forgeDomainId): bool;

    public function activateCertificate(int $forgeDomainId): void;

    public function deleteDomain(int $forgeDomainId): void;

    /** @return array<int,int> */
    public function listDomainIds(): array;
}
