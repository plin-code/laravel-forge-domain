<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Support;

use PlinCode\ForgeDomain\Contracts\DnsResolver;

final class PhpDnsResolver implements DnsResolver
{
    public function cname(string $host): array
    {
        return $this->lookup($host, DNS_CNAME, 'target');
    }

    public function a(string $host): array
    {
        return $this->lookup($host, DNS_A, 'ip');
    }

    public function txt(string $host): array
    {
        return $this->lookup($host, DNS_TXT, 'txt');
    }

    /**
     * @return array<int,string>
     */
    private function lookup(string $host, int $type, string $key): array
    {
        $records = @dns_get_record($host, $type);

        if ($records === false) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (array $record): ?string => isset($record[$key]) ? (string) $record[$key] : null,
            $records,
        )));
    }
}
