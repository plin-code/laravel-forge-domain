<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use PlinCode\LaravelForgeDomain\Contracts\DomainProvisioner;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\Drivers\ForgeProvisioner;
use PlinCode\LaravelForgeDomain\Drivers\WildcardProvisioner;
use Psr\Log\LoggerInterface;

final class DomainProvisioningManager
{
    /** @param array<string,mixed> $config */
    public function __construct(
        private readonly Container $app,
        private array $config,
    ) {}

    public function for(ProvisionableDomain $domain): DomainProvisioner
    {
        $name = $this->config['drivers'][$domain->getKind()->value] ?? null;

        if (! is_string($name)) {
            throw new InvalidArgumentException("No driver mapped for kind [{$domain->getKind()->value}].");
        }

        return $this->driver($name);
    }

    public function driver(string $name): DomainProvisioner
    {
        return match ($name) {
            'forge' => new ForgeProvisioner(
                $this->app->make(ForgeClient::class),
                (bool) $this->config['manage'],
                (int) $this->config['ssl']['active_days'],
                $this->app->make(LoggerInterface::class),
            ),
            'wildcard' => new WildcardProvisioner,
            default => throw new InvalidArgumentException("Unknown provisioner driver [{$name}]."),
        };
    }
}
