<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void onboard(\PlinCode\ForgeDomain\Contracts\ProvisionableDomain $domain)
 * @method static void provision(\PlinCode\ForgeDomain\Contracts\ProvisionableDomain $domain)
 * @method static void remove(\PlinCode\ForgeDomain\Contracts\ProvisionableDomain $domain)
 *
 * @see \PlinCode\ForgeDomain\ForgeDomain
 */
final class ForgeDomain extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'forge-domain';
    }
}
