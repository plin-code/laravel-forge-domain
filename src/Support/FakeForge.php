<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Support;

use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;

final class FakeForge implements ForgeClient
{
    private int $nextId = 1;

    /** @var array<int,string> */
    public array $created = [];

    /** @var array<int,bool> */
    public array $active = [];

    /** @var array<int,bool> */
    public array $activated = [];

    public function createDomain(string $hostname, bool $allowWildcard = false): int
    {
        $id = $this->nextId++;
        $this->created[$id] = $hostname;
        $this->active[$id] = false;

        return $id;
    }

    public function createCertificate(int $forgeDomainId): void
    {
        $this->active[$forgeDomainId] = false;
    }

    public function certificateIsActive(int $forgeDomainId): bool
    {
        return $this->active[$forgeDomainId] ?? false;
    }

    public function activateCertificate(int $forgeDomainId): void
    {
        $this->activated[$forgeDomainId] = true;
    }

    public function deleteDomain(int $forgeDomainId): void
    {
        unset($this->created[$forgeDomainId], $this->active[$forgeDomainId]);
    }

    public function listDomainIds(): array
    {
        return array_keys($this->created);
    }

    public function setActive(int $forgeDomainId, bool $active): void
    {
        $this->active[$forgeDomainId] = $active;
    }
}
