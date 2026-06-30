<?php

declare(strict_types=1);

use PlinCode\ForgeDomain\Support\ReconcileReport;

it('holds orphaned and missing forge ids', function (): void {
    $report = new ReconcileReport(orphanedInForge: [10, 11], missingInForge: [20]);

    expect($report->orphanedInForge)->toBe([10, 11])
        ->and($report->missingInForge)->toBe([20]);
});
