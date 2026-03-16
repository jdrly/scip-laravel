# Release and Distribution

## Supported delivery formats

`scip-laravel` currently supports these release-oriented distribution formats:
- Docker image
- standalone tarball bundle

The tarball is the current "PHAR or equivalent standalone artifact" for this project. It packages:
- `bin/`
- `src/`
- `vendor/`
- `composer.json`
- `composer.lock`
- `README.md`

## Versioning strategy

This project follows a simple SemVer-style release strategy:
- `MAJOR` for intentional breaking CLI, output, or packaging changes
- `MINOR` for new indexing capabilities and backward-compatible CLI additions
- `PATCH` for bug fixes, packaging fixes, and quality improvements

Pre-release versions can use a `-dev` suffix during active development.

## Changelog policy

Changelog entries should be written using these sections:
- Added
- Changed
- Fixed
- Removed

Conventional Commits should remain the source input for changelog updates.

## Build commands

### Build Docker runtime image

```bash
bash tools/release/build-runtime-image.sh 8.5
```

### Build standalone bundle

```bash
bash tools/release/build-standalone.sh
```

## Smoke tests

### Docker smoke test

```bash
bash tools/release/smoke-test-runtime-image.sh 8.5
```

### Standalone bundle smoke test

```bash
bash tools/release/smoke-test-standalone.sh
```

### Run all release artifact smoke tests

```bash
bash tools/release/smoke-test-release-artifacts.sh
```
