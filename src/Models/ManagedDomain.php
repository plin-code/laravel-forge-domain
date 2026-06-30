<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use PlinCode\LaravelForgeDomain\Concerns\HasProvisionableDomain;
use PlinCode\LaravelForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\LaravelForgeDomain\Database\Factories\ManagedDomainFactory;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\DomainStatus;
use PlinCode\LaravelForgeDomain\Support\VerificationMethod;

/**
 * @property string $hostname
 * @property DomainKind $kind
 * @property DomainStatus $status
 * @property VerificationMethod|null $verification_method
 * @property string|null $verification_token
 * @property string|null $dns_target
 * @property int|null $forge_domain_id
 * @property Carbon|null $ssl_expires_at
 */
class ManagedDomain extends Model implements ProvisionableDomain
{
    /** @use HasFactory<ManagedDomainFactory> */
    use HasFactory;

    use HasProvisionableDomain;
    use HasUuids;

    protected $table = 'forge_domains';

    protected $guarded = [];

    protected $casts = [
        'kind' => DomainKind::class,
        'status' => DomainStatus::class,
        'verification_method' => VerificationMethod::class,
        'ssl_expires_at' => 'datetime',
    ];

    public function getKey(): mixed
    {
        return parent::getKey();
    }

    protected static function newFactory(): ManagedDomainFactory
    {
        return ManagedDomainFactory::new();
    }
}
