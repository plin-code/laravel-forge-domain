<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\DnsVerifierManager;
use PlinCode\ForgeDomain\Events\DomainFailed;
use PlinCode\ForgeDomain\Events\DomainVerified;
use PlinCode\ForgeDomain\Support\DomainKind;

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
