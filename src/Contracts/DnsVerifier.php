<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Contracts;

interface DnsVerifier
{
    public function verify(ProvisionableDomain $domain): bool;
}
