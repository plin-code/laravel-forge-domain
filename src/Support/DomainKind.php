<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Support;

enum DomainKind: string
{
    case Custom = 'custom';
    case Subdomain = 'subdomain';
}
