<?php

declare(strict_types=1);

use Laravel\Forge\Forge;
use PlinCode\LaravelForgeDomain\Support\ForgeSdkClient;

beforeEach(function (): void {
    if (getenv('FORGE_DOMAIN_INTEGRATION') !== '1') {
        $this->markTestSkipped('Set FORGE_DOMAIN_INTEGRATION=1 to run the live Forge test.');
    }
});

it('attaches a hostname and issues an active certificate', function (): void {
    $client = new ForgeSdkClient(
        new Forge((string) getenv('FORGE_DOMAIN_TOKEN')),
        (string) getenv('FORGE_DOMAIN_ORGANIZATION'),
        (int) getenv('FORGE_DOMAIN_SERVER_ID'),
        (int) getenv('FORGE_DOMAIN_SITE_ID'),
    );

    $hostname = 'it-'.time().'.'.getenv('FORGE_DOMAIN_TEST_BASE');
    $id = $client->createDomain($hostname);

    $active = false;
    try {
        $client->createCertificate($id);
        for ($i = 0; $i < 20; $i++) {
            if ($client->certificateIsActive($id)) {
                $client->activateCertificate($id); // confirms the ACTIVATE_VERB
                $active = true;
                break;
            }
            sleep(15);
        }
    } finally {
        $client->deleteDomain($id);
    }

    expect($active)->toBeTrue();
});
