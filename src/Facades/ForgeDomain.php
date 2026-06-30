<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void onboard(\PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain $domain)
 * @method static void provision(\PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain $domain)
 * @method static void remove(\PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain $domain)
 *
 * @see \PlinCode\LaravelForgeDomain\ForgeDomain
 */
final class ForgeDomain extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'forge-domain';
    }
}
