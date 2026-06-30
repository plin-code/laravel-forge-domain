<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PlinCode\LaravelForgeDomain\Contracts\ForgeClient;
use PlinCode\LaravelForgeDomain\DomainProvisioningManager;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;

final class ReconcileDomainsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(DomainProvisioningManager $provisioners): void
    {
        // When the kill-switch is off, skip both the Forge API read and any
        // cleanup deletes so that management-disabled environments are fully inert.
        if (! config('forge-domain.manage')) {
            return;
        }

        /** @var class-string<ManagedDomain> $modelClass */
        $modelClass = config('forge-domain.models.managed_domain');

        $domains = $modelClass::query()
            ->where('kind', DomainKind::Custom->value)
            ->whereNotNull('forge_domain_id')
            ->get()
            ->all();

        $report = $provisioners->driver('forge')->reconcile($domains);

        if ($report->orphanedInForge === [] && $report->missingInForge === []) {
            return;
        }

        // WARNING: cleanup mode deletes every Forge domain on the configured site
        // that this package does not track. Only enable it when the Forge site is
        // dedicated exclusively to package-managed domains. Any manually created
        // domain on that site will be permanently deleted without further warning.
        if (config('forge-domain.reconcile.mode') === 'cleanup') {
            $forge = app(ForgeClient::class);
            foreach ($report->orphanedInForge as $forgeDomainId) {
                $forge->deleteDomain($forgeDomainId);
            }

            return;
        }

        Log::warning('forge-domain reconciliation found drift', [
            'orphaned_in_forge' => $report->orphanedInForge,
            'missing_in_forge' => $report->missingInForge,
        ]);
    }
}
