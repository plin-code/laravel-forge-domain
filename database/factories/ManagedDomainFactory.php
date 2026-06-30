<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;

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
