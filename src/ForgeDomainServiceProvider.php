<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain;

use Illuminate\Contracts\Container\Container;
use Laravel\Forge\Forge;
use PlinCode\LaravelForgeDomain\Commands\ReconcileDomainsCommand;
use PlinCode\LaravelForgeDomain\Commands\RenewSslCommand;
use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\Support\ForgeSdkClient;
use PlinCode\LaravelForgeDomain\Support\PhpDnsResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ForgeDomainServiceProvider extends PackageServiceProvider
{
    #[\Override]
    public function configurePackage(Package $package): void
    {
        $package
            ->name('forge-domain')
            ->hasConfigFile()
            ->hasMigration('create_forge_domain_table')
            ->hasCommand(RenewSslCommand::class)
            ->hasCommand(ReconcileDomainsCommand::class);
    }

    #[\Override]
    public function packageRegistered(): void
    {
        $this->app->bind(DnsResolver::class, PhpDnsResolver::class);

        $this->app->singleton(ForgeClient::class, function (): ForgeClient {
            /** @var array<string,mixed> $forgeConfig */
            $forgeConfig = config('forge-domain.forge', []);

            return new ForgeSdkClient(
                new Forge((string) $forgeConfig['token']),
                (string) $forgeConfig['organization'],
                (int) $forgeConfig['server_id'],
                (int) $forgeConfig['site_id'],
            );
        });

        $this->app->singleton(
            DomainProvisioningManager::class,
            function (Container $app): DomainProvisioningManager {
                /** @var array<string,mixed> $cfg */
                $cfg = config('forge-domain', []);

                return new DomainProvisioningManager($app, $cfg);
            },
        );

        $this->app->singleton(DnsVerifierManager::class, function (Container $app): DnsVerifierManager {
            /** @var array<string,mixed> $cfg */
            $cfg = config('forge-domain', []);
            $cfg['verification']['server_ip'] ??= $cfg['forge']['server_ip'] ?? null;

            return new DnsVerifierManager($app, $cfg);
        });

        $this->app->singleton(
            'forge-domain',
            fn (Container $app): ForgeDomain => new ForgeDomain(
                $app->make(DomainProvisioningManager::class),
            ),
        );

        $this->app->alias('forge-domain', ForgeDomain::class);
    }
}
