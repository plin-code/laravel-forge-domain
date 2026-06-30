# Changelog

All notable changes to `plin-code/laravel-forge-domain` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v0.1.0 - 2026-06-30

Initial release.

A reusable Laravel package that onboards tenant and customer hostnames with DNS ownership verification (CNAME or TXT) and Laravel Forge SSL provisioning, through interchangeable drivers. The `forge` driver attaches a customer custom hostname to a Forge site and issues a Let's Encrypt certificate. The `wildcard` driver serves platform tenant subdomains from a pre existing wildcard with no external call.

### Features

- Storage agnostic via the `ProvisionableDomain` contract, with an optional `ManagedDomain` model and a swappable `models.managed_domain` config.
- Async pipeline: verify, provision, confirm SSL, renew, remove, reconcile, with lifecycle events.
- `manage` kill switch that suppresses all Forge writes when off.
- Read only DNS verification (never writes DNS records).

### Requirements

PHP 8.3 or higher, Laravel 12 or 13.

### Note (pre 1.0)

The Forge certificate activation verb is not yet confirmed against a live Forge account, and the flow has not run in production. Run the gated integration test (`FORGE_DOMAIN_INTEGRATION=1`, or the `integration-tests` workflow) against a real Forge account to confirm it, then enable `FORGE_DOMAIN_MANAGE=true`.

## [Unreleased]
