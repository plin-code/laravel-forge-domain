<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Contracts\DnsResolver;
use PlinCode\LaravelForgeDomain\DnsVerifierManager;
use PlinCode\LaravelForgeDomain\Models\ManagedDomain;
use PlinCode\LaravelForgeDomain\Support\DomainKind;
use PlinCode\LaravelForgeDomain\Support\FakeDnsResolver;
use PlinCode\LaravelForgeDomain\Support\VerificationMethod;
use PlinCode\LaravelForgeDomain\Verifiers\CnameVerifier;
use PlinCode\LaravelForgeDomain\Verifiers\TxtVerifier;

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

it('rejects a txt token that does not match', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setTxt('_forge-verify.acme.io', ['other']);
    $domain = ManagedDomain::create([
        'hostname' => 'acme.io',
        'kind' => DomainKind::Custom,
        'verification_token' => 'tok-123',
    ]);

    expect((new TxtVerifier($dns, '_forge-verify'))->verify($domain))->toBeFalse();
});

it('rejects when the verification token is null', function (): void {
    $dns = new FakeDnsResolver;
    $domain = ManagedDomain::create([
        'hostname' => 'acme.io',
        'kind' => DomainKind::Custom,
    ]);

    expect((new TxtVerifier($dns, '_forge-verify'))->verify($domain))->toBeFalse();
});

it('falls back to the a record when the server ip matches', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setA('app.acme.com', ['1.2.3.4']);
    $domain = ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
    ]);

    expect((new CnameVerifier($dns, 'custom.platform.test', '1.2.3.4'))->verify($domain))->toBeTrue();
});

it('rejects the a-record fallback when the ip does not match', function (): void {
    $dns = new FakeDnsResolver;
    $dns->setA('app.acme.com', ['9.9.9.9']);
    $domain = ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
    ]);

    expect((new CnameVerifier($dns, 'custom.platform.test', '1.2.3.4'))->verify($domain))->toBeFalse();
});

it('resolves the cname verifier by config default', function (): void {
    $this->app->instance(DnsResolver::class, new FakeDnsResolver);
    $manager = new DnsVerifierManager($this->app, config('forge-domain'));
    $domain = ManagedDomain::create([
        'hostname' => 'app.acme.com',
        'kind' => DomainKind::Custom,
    ]);

    expect($manager->for($domain))->toBeInstanceOf(CnameVerifier::class);
});

it('resolves the txt verifier when method is txt', function (): void {
    $this->app->instance(DnsResolver::class, new FakeDnsResolver);
    $manager = new DnsVerifierManager($this->app, config('forge-domain'));

    expect($manager->driver(VerificationMethod::Txt))->toBeInstanceOf(TxtVerifier::class);
});
