<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Commands;

use Illuminate\Console\Command;
use PlinCode\LaravelForgeDomain\Jobs\ReconcileDomainsJob;

final class ReconcileDomainsCommand extends Command
{
    protected $signature = 'forge-domain:reconcile';

    protected $description = 'Reconcile stored domains against Forge';

    public function handle(): int
    {
        ReconcileDomainsJob::dispatch();

        return self::SUCCESS;
    }
}
