<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Support;

final readonly class ReconcileReport
{
    /**
     * @param  array<int,int>  $orphanedInForge
     * @param  array<int,int>  $missingInForge
     */
    public function __construct(
        public array $orphanedInForge,
        public array $missingInForge,
    ) {}
}
