<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain;

use Illuminate\Contracts\Container\Container;
use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\Contracts\DnsVerifier;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\Support\VerificationMethod;
use PlinCode\LaravelForgeDomain\Verifiers\CnameVerifier;
use PlinCode\LaravelForgeDomain\Verifiers\TxtVerifier;

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
