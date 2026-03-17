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
- Phase 6 Laravel 12 and 13 fixtures plus project detection coverage
- Phase 7 route-to-controller and route-to-method linking for supported static Laravel route forms
- Phase 8 provider registration and container binding extraction for supported static service-provider patterns

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

## Canonical install story

`scip-laravel` is being built as a standalone tool with this install and distribution order:
1. PHAR as the primary standalone artifact
2. GitHub Releases as the primary publication channel
3. Homebrew tap as the convenience install for macOS users
4. Docker image as the CI-safe fallback
5. standalone tarball bundle as a backup artifact only

That is the canonical direction for the project. It keeps the install surface small and avoids adding `scip-laravel` to the target Laravel application's Composer graph.

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

Laravel fixture projects for versions 12 and 13 now live in `fixtures/laravel12-app` and `fixtures/laravel13-app` and are used to validate framework detection, route analysis, provider analysis, Eloquent relation linking, and console-entrypoint behavior.

## Cross-version validation

This repository supports local Docker-based validation for both supported PHP runtimes:

```bash
composer check-docker-8.4
composer check-docker-8.5
composer check-matrix
```

These commands build a clean container, install dependencies, and run the full quality gate inside PHP 8.4 and PHP 8.5 environments.

## Release artifacts

Current release-oriented commands:
- `composer build-phar`
- `composer build-runtime-image`
- `composer build-standalone`
- `composer build-release-artifacts`
- `composer smoke-test-phar`
- `composer smoke-test-runtime-image`
- `composer smoke-test-standalone`
- `composer smoke-test-release-artifacts`

These scripts now cover the PHAR, Docker runtime image, and backup standalone tarball bundle. GitHub Releases publication is the next planned step so the install story can converge on one obvious public distribution path.

See `docs/releases.md` for the release workflow, versioning strategy, and distribution plan.

## Installation and distribution

### Recommended install order

For end users, the install guidance is:
1. PHAR from GitHub Releases
2. Homebrew tap
3. Docker image for CI-safe and isolated execution
4. standalone tarball bundle only when the primary artifacts are not suitable

### Primary install path: PHAR from GitHub Releases

This is the canonical install story for the project once GitHub Releases publication lands:

```bash
curl -L <release-url>/scip-laravel.phar -o /usr/local/bin/scip-laravel
chmod +x /usr/local/bin/scip-laravel
scip-laravel index
```

GitHub Releases is the primary publication channel for that artifact.

For local packaging work today, build the PHAR directly from the repository:

```bash
composer build-phar
composer smoke-test-phar
```

### CI-safe fallback: Docker image

Build a runtime image locally:

```bash
composer build-runtime-image
```

Run the image against a project mounted from the host:

```bash
docker run --rm \
  -v /path/to/project:/workspace/project:ro \
  -v /path/to/out:/workspace/out \
  scip-laravel:8.5 \
  index \
  --project-dir /workspace/project \
  --output /workspace/out/index.scip \
  --format scip \
  --framework laravel
```

### Backup artifact: standalone tarball bundle

The standalone tarball is kept as a backup release artifact. It is not the recommended day-to-day install path.

Build a standalone tarball bundle locally:

```bash
composer build-standalone
```

Smoke-test the generated bundle:

```bash
composer smoke-test-standalone
```

### Current implementation status

Today this repository can build the PHAR, Docker image, and standalone tarball locally. GitHub Releases publication and Homebrew distribution are still future phases.

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
- `composer test-dora`
- `composer build-phar`
- `composer build-runtime-image`
- `composer build-standalone`
- `composer build-release-artifacts`
- `composer smoke-test-phar`
- `composer smoke-test-runtime-image`
- `composer smoke-test-standalone`
- `composer smoke-test-release-artifacts`
- `composer check-docker-8.4`
- `composer check-docker-8.5`
- `composer check-matrix`
- `composer lint`
- `composer check`

## Notes

The old `scip-php` repository is used only as a reference source for ideas and test strategy. This project is being built as its own standalone implementation.
