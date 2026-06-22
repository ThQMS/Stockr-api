# Contributing to Stockr API

Thanks for your interest in contributing! This project follows **Domain-Driven
Design with 4 explicit layers** and keeps a high quality bar (PHPStan level 8,
Pest tests, architecture tests). Please read this before opening a PR.

## Getting set up

See [`docs/01-getting-started.md`](docs/01-getting-started.md). In short:

```bash
composer install
php artisan key:generate
php artisan migrate
```

> On a machine without a global Composer, use `php composer.phar <command>`.

## Project conventions

The layering is enforced by executable architecture tests
(`tests/Unit/ArchitectureTest.php`). When adding a feature, respect the flow:

1. **Domain first** — model the entity / value object / event in `src/Domain`,
   with **no `Illuminate` imports**.
2. **Use case** in `src/Application` with a single `execute(DTO): DTO`, depending
   on **interfaces** (never Eloquent).
3. **Adapter** in `src/Infrastructure` implementing the new port; register the
   binding in `app/Providers/AppServiceProvider.php`.
4. **Thin controller** + Form Request (with Policy) + Resource in
   `src/Presentation`.
5. **Tests**: unit for the domain, feature for the endpoint.

More detail in [`docs/02-architecture.md`](docs/02-architecture.md).

## Before opening a PR

Run the full quality gate locally — CI runs the same checks:

```bash
vendor/bin/pint --test                              # code style (PSR-12)
php vendor/phpstan/phpstan/phpstan.phar analyse     # static analysis (level 8)
php vendor/pestphp/pest/bin/pest                     # tests
```

All three must pass. New behaviour should come with tests.

## Pull requests

- Keep PRs focused; one logical change per PR.
- Write a clear description of **what** and **why**.
- Reference any related issue (`Closes #123`).
- Don't commit generated artifacts (`composer.phar`, `database/database.sqlite`,
  `_ide_helper*.php`) — they are gitignored.

## Commit messages

Use clear, imperative messages (e.g. `Add batch movement sync endpoint`).
Conventional Commits are welcome but not required.

## Reporting bugs / requesting features

Open an issue using the templates under `.github/ISSUE_TEMPLATE`. For security
issues, **do not** open a public issue — see [`SECURITY.md`](SECURITY.md).
