# Contributing

Contributions are welcome. Please follow the steps below to get started.

## Setup

Clone the repository and install dependencies:

```bash
git clone https://github.com/plin-code/laravel-forge-domain.git
cd forge-domain
composer install
```

## Tests

Run the full Pest test suite:

```bash
composer test
```

## Static Analysis

Run PHPStan at level 7:

```bash
composer analyse
```

## Code Style

Format with Laravel Pint (PSR-12 plus Laravel conventions):

```bash
composer format
```

To check formatting without writing changes:

```bash
vendor/bin/pint --test
```

## Pull Requests

1. Fork the repository and create a branch from `main`.
2. Write or update tests for any changed behaviour.
3. Run `composer test`, `composer analyse`, and `composer format` before pushing.
4. Open a pull request with a clear description of the change and its motivation.

Please keep pull requests focused on a single concern. Unrelated changes should be submitted separately.
