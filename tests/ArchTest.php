<?php

declare(strict_types=1);

arch('no debug helpers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('strict types everywhere')
    ->expect('PlinCode\LaravelForgeDomain')
    ->toUseStrictTypes();

arch('drivers implement the provisioner contract')
    ->expect('PlinCode\LaravelForgeDomain\Drivers')
    ->toImplement('PlinCode\LaravelForgeDomain\Contracts\DomainProvisioner');

arch('verifiers implement the verifier contract')
    ->expect('PlinCode\LaravelForgeDomain\Verifiers')
    ->toImplement('PlinCode\LaravelForgeDomain\Contracts\DnsVerifier');
