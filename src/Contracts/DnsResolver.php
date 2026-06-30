<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Contracts;

interface DnsResolver
{
    /** @return array<int,string> */
    public function cname(string $host): array;

    /** @return array<int,string> */
    public function a(string $host): array;

    /** @return array<int,string> */
    public function txt(string $host): array;
}
