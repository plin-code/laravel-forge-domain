<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PlinCode\ForgeDomain\Concerns\HasProvisionableDomain;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Database\Factories\ManagedDomainFactory;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\VerificationMethod;

class ManagedDomain extends Model implements ProvisionableDomain
{
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
