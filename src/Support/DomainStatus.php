<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Support;

enum DomainStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Provisioning = 'provisioning';
    case Active = 'active';
    case Failed = 'failed';
    case Removed = 'removed';
}
