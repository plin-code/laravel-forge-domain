<?php

declare(strict_types=1);

namespace PlinCode\LaravelForgeDomain\Support;

enum VerificationMethod: string
{
    case Cname = 'cname';
    case Txt = 'txt';
}
