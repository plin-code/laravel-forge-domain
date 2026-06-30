<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Events\DomainRemoved;
use Throwable;

final class RemoveDomainJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ProvisionableDomain $domain) {}

    public function handle(DomainProvisioningManager $provisioners): void
    {
        try {
            $provisioners->for($this->domain)->remove($this->domain);
        } catch (Throwable) {
            RetryCleanupJob::dispatch($this->domain)->delay(now()->addSeconds(60));

            return;
        }

        $this->domain->markRemoved();
        event(new DomainRemoved($this->domain));
    }
}
