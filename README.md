# scip-laravel

`scip-laravel` is a standalone SCIP indexer for Laravel applications.

The goal of this project is to generate high-quality SCIP indexes for modern Laravel codebases so they can be consumed by tools like Dora and other SCIP-compatible tooling.

## Status

This project is in early development.

The repository is currently bootstrapped with:
- standalone package identity
- CLI entrypoint skeleton
- baseline test tooling
- CI setup
- initial project structure

The actual Laravel-aware indexing implementation is still in progress.

## Scope

The target scope for this project is:
- PHP 8.4 and 8.5
- Laravel 12 and 13

This project is intended to run as a standalone tool. It should not require being added to the target Laravel application's dependency graph.

## Minimal requirements

### Runtime requirements

For using `scip-laravel`:
- PHP 8.4.1 or newer
- a Laravel 12 or Laravel 13 application to index
- the target application should have a normal Composer-based structure

### Development requirements

For working on this repository itself:
- PHP 8.5 is recommended locally
- Composer 2.x

## Current compatibility policy

This project intentionally targets maintained modern runtimes.

That currently means:
- PHP 8.4.x
- PHP 8.5.x

Older PHP versions are out of scope.

## Planned delivery model

`scip-laravel` is being built as a standalone tool, with these delivery modes in mind:
- Docker image
- PHAR
- isolated global Composer install
- local repository checkout

It is explicitly **not** being designed as a normal `require-dev` package inside every indexed Laravel app.

## Development

Install dependencies:

```bash
composer install
```

Run the current project checks:

```bash
composer check
```

Available scripts:
- `composer composer-validate`
- `composer phpstan`
- `composer phpcs`
- `composer test`
- `composer lint`
- `composer check`

## Notes

The old `scip-php` repository is used only as a reference source for ideas and test strategy. This project is being built as its own standalone implementation.
