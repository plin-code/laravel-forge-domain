<?php

declare(strict_types=1);

namespace PlinCode\ForgeDomain\Support;

enum VerificationMethod: string
{
    case Cname = 'cname';
    case Txt = 'txt';
}
