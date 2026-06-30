<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Verifiers;

use PlinCode\ForgeDomain\Contracts\DnsResolver;
use PlinCode\ForgeDomain\Contracts\DnsVerifier;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;

final class CnameVerifier implements DnsVerifier
{
    public function __construct(
        private DnsResolver $dns,
        private string $cnameTarget,
        private ?string $serverIp,
    ) {}

    public function verify(ProvisionableDomain $domain): bool
    {
        $host = $domain->getHostname();
        $target = rtrim($this->cnameTarget, '.');

        foreach ($this->dns->cname($host) as $cname) {
            if (rtrim($cname, '.') === $target) {
                return true;
            }
        }

        if ($this->serverIp !== null) {
            return in_array($this->serverIp, $this->dns->a($host), true);
        }

        return false;
    }
}
