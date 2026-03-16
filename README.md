# scip-laravel

`scip-laravel` is a standalone SCIP indexer for Laravel applications.

The goal of this project is to generate high-quality SCIP indexes for modern Laravel codebases so they can be consumed by tools like Dora and other SCIP-compatible tooling.

## Status

This project is in early development.

The repository is currently bootstrapped with:
- standalone package identity
- a working `index` CLI command
- config-file aware command execution
- CI setup
- initial project structure
- Phase 1 test harness inspired by the original `scip-php` suite
- unit, integration, and snapshot test layers
- a minimal plain PHP fixture used for deterministic regression checks
- a Phase 2 core PHP indexing foundation
- deterministic JSON-based index snapshots for the plain PHP fixture
- Phase 4 parsing utilities for PHPDoc v2 and builtin symbol stub lookups

The actual Laravel-aware indexing implementation is still in progress.

## Scope

The target scope for this project is:
- PHP 8.4 and 8.5
- Laravel 12 and 13

This project is intended to run as a standalone tool. It should not require being added to the target Laravel application's dependency graph.

The current tooling and tests are designed so indexing works against project paths outside this repository, which is important for local installs, Docker execution, and future packaged distributions.

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

## CLI usage

Current command shape:

```bash
./bin/scip-laravel index \
  --project-dir /path/to/project \
  --output /path/to/index.json
```

Additional options:
- `--framework=auto|php|laravel`
- `--php-version=auto|8.4|8.5`
- `--memory-limit=1G`
- `--config=/path/to/scip-laravel.json`
- Symfony Console global verbosity flags like `-v`, `-vv`, and `-vvv`

Supported config file formats:
- `.json`
- `.php` returning an array

Config file keys:
- `projectDir`
- `output`
- `framework`
- `phpVersion`
- `memoryLimit`

CLI options override config file values.

### Important current limitation

At this stage, the `index` command writes a deterministic JSON representation of the current internal index model.

That is intentional for early development and regression testing. Final protobuf-backed SCIP output will come in later phases.

Laravel fixture projects for versions 12 and 13 now live in `fixtures/laravel12-app` and `fixtures/laravel13-app` and are used to validate framework detection and fixture indexing behavior.

## Cross-version validation

This repository supports local Docker-based validation for both supported PHP runtimes:

```bash
composer check-docker-8.4
composer check-docker-8.5
composer check-matrix
```

These commands build a clean container, install dependencies, and run the full quality gate inside PHP 8.4 and PHP 8.5 environments.

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
- `composer test-unit`
- `composer test-integration`
- `composer test-snapshot`
- `composer lint`
- `composer check`

## Notes

The old `scip-php` repository is used only as a reference source for ideas and test strategy. This project is being built as its own standalone implementation.
