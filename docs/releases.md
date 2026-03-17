# Release and Distribution

## Canonical distribution hierarchy

`scip-laravel` is moving toward one clear install story:
1. PHAR as the primary standalone artifact
2. GitHub Releases as the primary publication channel
3. Homebrew tap as the convenience install for macOS users
4. Docker image as the CI-safe fallback
5. standalone tarball bundle as a backup artifact only

This keeps the tool out of the indexed application's Composer graph while still giving users a short, obvious install path.

## Current implementation status

Today the repository can build and smoke-test these release-oriented artifacts:
- PHAR
- Docker runtime image
- standalone tarball bundle

GitHub Releases publication is planned next. The tarball remains a backup artifact rather than the main installation path.

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

## Publication model

GitHub Releases is the primary publication channel.

The intended release payload is:
- `scip-laravel.phar`
- checksums
- standalone tarball bundle as backup
- release notes or changelog excerpt

Docker images remain important, but they are positioned as the reliable fallback for CI and other isolated execution environments.

## Build commands

### Build PHAR

```bash
php -d phar.readonly=0 tools/release/build-phar.php
```

### Build Docker runtime image

```bash
bash tools/release/build-runtime-image.sh 8.5
```

### Build standalone bundle

```bash
bash tools/release/build-standalone.sh
```

### Build all current release artifacts

```bash
bash tools/release/build-release-artifacts.sh
```

## Smoke tests

### PHAR smoke test

```bash
bash tools/release/smoke-test-phar.sh
```

The PHAR smoke test exercises:
- `fixtures/plain-php-modern`
- `fixtures/laravel12-app`
- `fixtures/laravel13-app`

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
