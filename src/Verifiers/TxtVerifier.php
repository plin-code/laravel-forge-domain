<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Verifiers;

use PlinCode\ForgeDomain\Contracts\DnsResolver;
use PlinCode\ForgeDomain\Contracts\DnsVerifier;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;

final class TxtVerifier implements DnsVerifier
{
    public function __construct(
        private DnsResolver $dns,
        private string $txtPrefix,
    ) {}

    public function verify(ProvisionableDomain $domain): bool
    {
        $token = $domain->getVerificationToken();

        if ($token === null) {
            return false;
        }

        $host = $this->txtPrefix.'.'.$domain->getHostname();

        return in_array($token, $this->dns->txt($host), true);
    }
}
