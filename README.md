# forge-domain

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plin-code/forge-domain.svg?style=flat-square)](https://packagist.org/packages/plin-code/forge-domain)
[![Tests](https://img.shields.io/github/actions/workflow/status/plin-code/forge-domain/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plin-code/forge-domain/actions/workflows/run-tests.yml)
[![Code Style](https://img.shields.io/github/actions/workflow/status/plin-code/forge-domain/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plin-code/forge-domain/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/plin-code/forge-domain.svg?style=flat-square)](https://packagist.org/packages/plin-code/forge-domain)

A Laravel package for onboarding tenant and customer hostnames with DNS verification and a Laravel Forge SSL provisioning flow.

## What it solves

Multi-tenant SaaS applications often allow customers to bring their own domains. This package automates the lifecycle from the moment a hostname is submitted until its SSL certificate is active and confirmed on Laravel Forge. It handles DNS verification (CNAME or TXT), Forge API calls, SSL polling, lifecycle events, reconciliation, and renewal, all behind a small, stable facade.

## Features

- DNS verification via CNAME or TXT record checks
- Laravel Forge provisioning with SSL creation and activation polling
- Wildcard driver for subdomain hostnames that do not need Forge provisioning
- Lifecycle events for verified, provisioning, activated, failed, and removed states
- Artisan commands for SSL renewal and domain reconciliation
- Shipped `ManagedDomain` Eloquent model (UUID primary key) or bring your own model via the `ProvisionableDomain` contract and the `HasProvisionableDomain` trait
- Test helpers: `FakeForge` and `FakeDnsResolver` for in-process testing without network calls
- Master kill-switch (`FORGE_DOMAIN_MANAGE`) so the package can be installed before Forge credentials exist

## Requirements

- PHP 8.3 or higher
- Laravel 12 or 13

## Installation

Install via Composer:

```bash
composer require plin-code/forge-domain
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="forge-domain-config"
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="forge-domain-migrations"
php artisan migrate
```

## Quick Start

Call `ForgeDomain::onboard()` after persisting the domain record. The facade dispatches a `VerifyDomainJob` that checks DNS, then hands off to the provisioning driver.

```php
use PlinCode\ForgeDomain\Facades\ForgeDomain;
use PlinCode\ForgeDomain\Models\ManagedDomain;
use PlinCode\ForgeDomain\Support\DomainKind;

$domain = ManagedDomain::create([
    'hostname' => 'app.customer.com',
    'kind'     => DomainKind::Custom,
]);

ForgeDomain::onboard($domain);
```

To remove a domain from Forge:

```php
ForgeDomain::remove($domain);
```

## Configuration

After publishing, the config lives at `config/forge-domain.php`.

### `drivers`

Maps each `DomainKind` value to a provisioner driver name. The defaults are:

```php
'drivers' => [
    'custom'    => 'forge',
    'subdomain' => 'wildcard',
],
```

### `manage`

Master kill-switch. When `false`, the forge driver logs operations instead of calling the Forge API. Useful before Forge credentials exist.

```
FORGE_DOMAIN_MANAGE=true
```

### `forge`

Forge API credentials and target server/site identifiers.

| Key | Env var | Description |
|---|---|---|
| `token` | `FORGE_DOMAIN_TOKEN` | Forge personal access token |
| `organization` | `FORGE_DOMAIN_ORGANIZATION` | Forge organization slug (optional) |
| `server_id` | `FORGE_DOMAIN_SERVER_ID` | ID of the target Forge server |
| `site_id` | `FORGE_DOMAIN_SITE_ID` | ID of the target Forge site |
| `server_ip` | `FORGE_DOMAIN_SERVER_IP` | Public IP of the server (used for A-record checks) |

### `verification`

Controls how DNS ownership is confirmed before provisioning.

| Key | Env var | Description |
|---|---|---|
| `method` | `FORGE_DOMAIN_VERIFICATION` | `cname` or `txt` (default `cname`) |
| `cname_target` | `FORGE_DOMAIN_CNAME_TARGET` | The CNAME value customers must point at |
| `txt_prefix` | (hardcoded) | Prefix for the TXT record name (default `_forge-verify`) |

### `ssl`

| Key | Default | Description |
|---|---|---|
| `active_days` | `90` | Expected SSL validity window in days |
| `renew_days_before` | `14` | Days before expiry at which renewal is triggered |
| `poll_tries` | `15` | Number of polling attempts when waiting for Forge to activate the certificate |
| `poll_backoff` | `30` | Seconds between polling attempts |

### `reconcile`

| Key | Default | Description |
|---|---|---|
| `mode` | `log` | Set to `cleanup` to have the reconciler delete orphaned Forge domains automatically |

### `models`

Swap out the shipped `ManagedDomain` model with your own:

```php
'models' => [
    'managed_domain' => \App\Models\Domain::class,
],
```

## Drivers

### `forge`

The `forge` driver calls the Laravel Forge API to create a domain entry and issue an SSL certificate for the hostname. It polls until the certificate is active, then dispatches `DomainActivated`. When `FORGE_DOMAIN_MANAGE` is `false` the driver logs each step and returns without touching the API.

### `wildcard`

The `wildcard` driver is a no-op provisioner intended for subdomains already covered by a wildcard SSL certificate. It transitions the domain directly to `active` without any Forge API calls.

### How `kind` maps to a driver

The `drivers` config key maps the string value of `DomainKind` to a driver name:

```php
// DomainKind::Custom->value === 'custom'  => 'forge'
// DomainKind::Subdomain->value === 'subdomain' => 'wildcard'
```

You can point either kind at a custom driver name and register your own `DomainProvisioner` implementation in the service container.

## Verification

Before provisioning, the package verifies DNS ownership using the method configured in `verification.method`.

**CNAME**: the verifier resolves the CNAME record for the hostname and checks it matches `verification.cname_target`.

**TXT**: the verifier resolves TXT records for `{txt_prefix}.{hostname}` and checks that one of them contains the domain's `verification_token`.

The domain model stores which method was requested in its `verification_method` column.

## Models

### Using the shipped `ManagedDomain`

The package ships `PlinCode\ForgeDomain\Models\ManagedDomain`, which uses UUID primary keys and the `forge_domains` table. It implements `ProvisionableDomain` via the `HasProvisionableDomain` trait and is ready to use out of the box.

### Bringing your own model

Implement `ProvisionableDomain` on any Eloquent model and add the `HasProvisionableDomain` trait for the default implementation:

```php
use Illuminate\Database\Eloquent\Model;
use PlinCode\ForgeDomain\Concerns\HasProvisionableDomain;
use PlinCode\ForgeDomain\Contracts\ProvisionableDomain;
use PlinCode\ForgeDomain\Support\DomainKind;
use PlinCode\ForgeDomain\Support\DomainStatus;
use PlinCode\ForgeDomain\Support\VerificationMethod;

class Domain extends Model implements ProvisionableDomain
{
    use HasProvisionableDomain;

    protected $casts = [
        'kind'                => DomainKind::class,
        'status'              => DomainStatus::class,
        'verification_method' => VerificationMethod::class,
        'ssl_expires_at'      => 'datetime',
    ];
}
```

Your table must include these columns: `hostname`, `kind`, `status`, `verification_method` (nullable), `verification_token` (nullable), `dns_target` (nullable), `forge_domain_id` (nullable unsigned bigint), `ssl_expires_at` (nullable timestamp), and `failure_reason` (nullable text).

Update the config to point at your model:

```php
'models' => [
    'managed_domain' => \App\Models\Domain::class,
],
```

### `ProvisionableDomain` contract

The interface that all domain models must satisfy:

```php
interface ProvisionableDomain
{
    public function getKey(): mixed;
    public function getHostname(): string;
    public function getKind(): DomainKind;
    public function getVerificationMethod(): ?VerificationMethod;
    public function getVerificationToken(): ?string;
    public function getDnsTarget(): ?string;
    public function getForgeDomainId(): ?int;
    public function setForgeDomainId(?int $id): void;
    public function getStatus(): DomainStatus;
    public function markVerified(): void;
    public function markProvisioning(): void;
    public function markSslActive(\DateTimeInterface $expiresAt): void;
    public function markFailed(string $reason): void;
    public function markRemoved(): void;
}
```

## Events

All events carry a public `$domain` property typed as `ProvisionableDomain`.

| Event | Fired when |
|---|---|
| `DomainVerified` | DNS verification passes |
| `DomainProvisioning` | Forge provisioning begins |
| `DomainActivated` | SSL certificate is confirmed active |
| `DomainFailed` | Any step fails permanently |
| `DomainRemoved` | The domain is deleted from Forge |

Register listeners in your `EventServiceProvider` or using `#[AsEventListener]`:

```php
use PlinCode\ForgeDomain\Events\DomainActivated;

public function handle(DomainActivated $event): void
{
    $event->domain->getHostname(); // 'app.customer.com'
}
```

## Commands

### `forge-domain:renew-ssl`

Queries for domains whose `ssl_expires_at` is within the configured `renew_days_before` window and dispatches `RenewSslJob` for each one. Run this on a daily schedule:

```php
// routes/console.php
Schedule::command('forge-domain:renew-ssl')->daily();
```

### `forge-domain:reconcile`

Dispatches `ReconcileDomainsJob`, which compares the domain IDs stored in your database against the domain IDs returned by the Forge API and reports (or cleans up) any divergence. When `reconcile.mode` is `log`, divergences are written to the application log. When set to `cleanup`, orphaned Forge domains are deleted.

```php
Schedule::command('forge-domain:reconcile')->weekly();
```

## Testing

The package ships two test fakes. Swap them in with Laravel's `bind` or `instance` helpers in your test setup.

### `FakeForge`

An in-memory `ForgeClient` that records creates, certificate state changes, and deletes without hitting the Forge API.

```php
use PlinCode\ForgeDomain\Support\FakeForge;
use PlinCode\ForgeDomain\Contracts\ForgeClient;

$fake = new FakeForge();
$this->app->instance(ForgeClient::class, $fake);

// Force a certificate to report as active
$fake->setActive($forgeDomainId, true);

// Assert a domain was created
expect($fake->created)->toHaveKey(1);
```

### `FakeDnsResolver`

An in-memory `DnsResolver` that lets you seed CNAME, A, and TXT records per hostname.

```php
use PlinCode\ForgeDomain\Support\FakeDnsResolver;
use PlinCode\ForgeDomain\Contracts\DnsResolver;

$resolver = new FakeDnsResolver();
$resolver->setCname('app.customer.com', ['proxy.myapp.com']);
$resolver->setTxt('_forge-verify.app.customer.com', ['forge-abc123']);

$this->app->instance(DnsResolver::class, $resolver);
```

Run the test suite:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Daniele Barbaro](https://github.com/plin-code)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
