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
use PlinCode\LaravelForgeDomain\Events\DomainFailed;
use PlinCode\LaravelForgeDomain\Events\DomainProvisioning;

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

    public function failed(\Throwable $exception): void
    {
        $this->domain->markFailed($exception->getMessage());
        event(new DomainFailed($this->domain, $exception->getMessage()));
    }
}
