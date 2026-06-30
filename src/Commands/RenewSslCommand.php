<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Commands;

use Illuminate\Console\Command;
use PlinCode\LaravelForgeDomain\Jobs\RenewSslJob;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;

final class RenewSslCommand extends Command
{
    protected $signature = 'forge-domain:renew-ssl';

    protected $description = 'Dispatch SSL renewal for domains expiring soon';

    public function handle(): int
    {
        /** @var class-string<ManagedDomain> $modelClass */
        $modelClass = config('forge-domain.models.managed_domain');
        $days = (int) config('forge-domain.ssl.renew_days_before', 14);

        $modelClass::query()
            ->whereNotNull('ssl_expires_at')
            ->where('ssl_expires_at', '<=', now()->addDays($days))
            ->each(static fn (ManagedDomain $domain) => RenewSslJob::dispatch($domain));

        return self::SUCCESS;
    }
}
