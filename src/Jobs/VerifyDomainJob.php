<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\DnsVerifierManager;
use PlinCode\LaravelForgeDomain\Events\DomainFailed;
use PlinCode\LaravelForgeDomain\Events\DomainVerified;
use PlinCode\LaravelForgeDomain\Support\DomainKind;

final class VerifyDomainJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(public ProvisionableDomain $domain) {}

    public function handle(DnsVerifierManager $verifiers): void
    {
        // Subdomains are platform-owned; the wildcard record and cert already cover
        // them, so no DNS verification is needed.
        if ($this->domain->getKind() === DomainKind::Subdomain) {
            $this->domain->markVerified();
            event(new DomainVerified($this->domain));
            ProvisionDomainJob::dispatch($this->domain);

            return;
        }

        if ($verifiers->for($this->domain)->verify($this->domain)) {
            $this->domain->markVerified();
            event(new DomainVerified($this->domain));
            ProvisionDomainJob::dispatch($this->domain);

            return;
        }

        if ($this->attempts() >= $this->tries) {
            $this->domain->markFailed('dns verification failed');
            event(new DomainFailed($this->domain, 'dns verification failed'));

            return;
        }

        $this->release(60);
    }
}
