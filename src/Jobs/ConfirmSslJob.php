<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;

// TODO(task-12): This is a minimal stub to allow ProvisionDomainJob to reference
// this class. Task 12 must replace this body with the real SSL polling logic.
final class ConfirmSslJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ProvisionableDomain $domain) {}

    public function handle(): void {}
}
