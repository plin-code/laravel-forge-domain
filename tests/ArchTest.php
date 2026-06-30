<?php

declare(strict_types=1);

arch('no debug helpers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('strict types everywhere')
    ->expect('PlinCode\ForgeDomain')
    ->toUseStrictTypes();

arch('drivers implement the provisioner contract')
    ->expect('PlinCode\ForgeDomain\Drivers')
    ->toImplement('PlinCode\ForgeDomain\Contracts\DomainProvisioner');

arch('verifiers implement the verifier contract')
    ->expect('PlinCode\ForgeDomain\Verifiers')
    ->toImplement('PlinCode\ForgeDomain\Contracts\DnsVerifier');
