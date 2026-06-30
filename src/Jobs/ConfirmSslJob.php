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
use PlinCode\LaravelForgeDomain\Events\DomainActivated;
use PlinCode\LaravelForgeDomain\Events\DomainFailed;

final class ConfirmSslJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Driven by forge-domain.ssl.poll_tries; deployments can raise it via config.
    // The release() backoff below paces polling.
    public int $tries;

    public function __construct(public ProvisionableDomain $domain)
    {
        $this->tries = (int) config('forge-domain.ssl.poll_tries', 15);
    }

    public function handle(DomainProvisioningManager $provisioners): void
    {
        if ($provisioners->for($this->domain)->confirm($this->domain)) {
            event(new DomainActivated($this->domain));

            return;
        }

        $this->release((int) config('forge-domain.ssl.poll_backoff', 30));
    }

    public function failed(\Throwable $exception): void
    {
        $this->domain->markFailed($exception->getMessage());
        event(new DomainFailed($this->domain, $exception->getMessage()));
    }
}
