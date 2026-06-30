<?php

declare(strict_types=1);

use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\FakeDnsResolver;
use PlinCode\ForgeDomain\Support\VerificationMethod;
use PlinCode\ForgeDomain\Verifiers\CnameVerifier;
use PlinCode\ForgeDomain\Verifiers\TxtVerifier;

it('verifies a cname pointing at the target', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setCname('app.acme.com', ['custom.platform.test']);
    $domain = ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
        'dns_target' => 'custom.platform.test',
    ]);

    $verifier = new CnameVerifier($dns, 'custom.platform.test', null);

    expect($verifier->verify($domain))->toBeTrue();
});

it('rejects a cname pointing elsewhere', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setCname('app.acme.com', ['somewhere.else']);
    $domain = ManagedDomain::create(['hostname' => 'app.acme.com', 'kind' => DomainKind::Custom]);

    expect((new CnameVerifier($dns, 'custom.platform.test', null))->verify($domain))->toBeFalse();
});

it('verifies a txt token at the prefixed host', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setTxt('_forge-verify.acme.io', ['tok-123']);
    $domain = ManagedDomain::create([
        'hostname' => 'acme.io',
        'kind' => DomainKind::Custom,
        'verification_token' => 'tok-123',
    ]);

    expect((new TxtVerifier($dns, '_forge-verify'))->verify($domain))->toBeTrue();
});
