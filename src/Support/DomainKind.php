<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Support;

enum DomainKind: string
{
    case Custom = 'custom';
    case Subdomain = 'subdomain';
}
