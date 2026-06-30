<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain;

use Illuminate\Contracts\Container\Container;
use PlinCode\ForgeDomain\Contracts\DnsResolver;
use PlinCode\ForgeDomain\Contracts\DnsVerifier;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Support\VerificationMethod;
use PlinCode\ForgeDomain\Verifiers\CnameVerifier;
use PlinCode\ForgeDomain\Verifiers\TxtVerifier;

final class DnsVerifierManager
{
    /** @param array<string,mixed> $config */
    public function __construct(
        private Container $app,
        private array $config,
    ) {}

    public function for(ProvisionableDomain $domain): DnsVerifier
    {
        $method = $domain->getVerificationMethod()
            ?? VerificationMethod::from((string) $this->config['verification']['method']);

        return $this->driver($method);
    }

    public function driver(VerificationMethod $method): DnsVerifier
    {
        $dns = $this->app->make(DnsResolver::class);

        return match ($method) {
            VerificationMethod::Cname => new CnameVerifier(
                $dns,
                (string) $this->config['verification']['cname_target'],
                $this->config['verification']['server_ip'] ?? null,
            ),
            VerificationMethod::Txt => new TxtVerifier(
                $dns,
                (string) $this->config['verification']['txt_prefix'],
            ),
        };
    }
}
