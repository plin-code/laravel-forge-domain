<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;

class ManagedDomainFactory extends Factory
{
    protected $model = ManagedDomain::class;

    public function definition(): array
    {
        return [
            'hostname' => $this->faker->unique()->domainName(),
            'kind' => DomainKind::Custom,
            'status' => DomainStatus::Pending,
        ];
    }
}
