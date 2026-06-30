<?php

declare(strict_types=1);

use PlinCode\LaravelForgeDomain\Support\FakeForge;

it('records domain and certificate lifecycle', function (): void {
    $forge = new FakeForge;

    $id = $forge->createDomain('app.acme.com');
    $forge->createCertificate($id);

    expect($forge->certificateIsActive($id))->toBeFalse()
        ->and($forge->listDomainIds())->toBe([$id]);

    $forge->setActive($id, true);
    $forge->activateCertificate($id);
    expect($forge->certificateIsActive($id))->toBeTrue();

    $forge->deleteDomain($id);
    expect($forge->listDomainIds())->toBe([]);
});
