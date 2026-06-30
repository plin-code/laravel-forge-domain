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
use PlinCode\ForgeDomain\Events\DomainProvisioning;

final class ProvisionDomainJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ProvisionableDomain $domain) {}

    public function handle(DomainProvisioningManager $provisioners): void
    {
        $this->domain->markProvisioning();
        event(new DomainProvisioning($this->domain));

        $provisioners->for($this->domain)->provision($this->domain);

        ConfirmSslJob::dispatch($this->domain)->delay(now()->addSeconds(30));
    }
}
