<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ForgeDomainServiceProvider extends PackageServiceProvider
{
    #[\Override]
    public function configurePackage(Package $package): void
    {
        $package
            ->name('forge-domain')
            ->hasConfigFile()
            ->hasMigration('create_forge_domain_table');
    }
}
