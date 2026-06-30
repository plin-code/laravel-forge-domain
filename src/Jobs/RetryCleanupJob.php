<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Events\DomainRemoved;

final class RetryCleanupJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(public ProvisionableDomain $domain) {}

    public function handle(DomainProvisioningManager $provisioners): void
    {
        $provisioners->for($this->domain)->remove($this->domain);
        $this->domain->markRemoved();
        event(new DomainRemoved($this->domain));
    }
}
