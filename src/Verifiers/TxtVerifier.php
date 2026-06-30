<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Verifiers;

use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\Contracts\DnsVerifier;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;

final readonly class TxtVerifier implements DnsVerifier
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
