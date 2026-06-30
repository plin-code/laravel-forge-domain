<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Support;

use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;

final class FakeDnsResolver implements DnsResolver
{
    /** @var array<string,array<int,string>> */
    private array $cname = [];

    /** @var array<string,array<int,string>> */
    private array $a = [];

    /** @var array<string,array<int,string>> */
    private array $txt = [];

    /** @param array<int,string> $records */
    public function setCname(string $host, array $records): void
    {
        $this->cname[$host] = $records;
    }

    /** @param array<int,string> $records */
    public function setA(string $host, array $records): void
    {
        $this->a[$host] = $records;
    }

    /** @param array<int,string> $records */
    public function setTxt(string $host, array $records): void
    {
        $this->txt[$host] = $records;
    }

    public function cname(string $host): array
    {
        return $this->cname[$host] ?? [];
    }

    public function a(string $host): array
    {
        return $this->a[$host] ?? [];
    }

    public function txt(string $host): array
    {
        return $this->txt[$host] ?? [];
    }
}
