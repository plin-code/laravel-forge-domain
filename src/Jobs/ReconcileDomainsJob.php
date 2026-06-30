<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PlinCode\ForgeDomain\Contracts\ForgeClient;
use PlinCode\ForgeDomain\DomainProvisioningManager;
use PlinCode\ForgeDomain\Support\DomainKind;

final class ReconcileDomainsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(DomainProvisioningManager $provisioners): void
    {
        /** @var class-string<Model> $modelClass */
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
