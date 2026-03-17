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
- standalone tarball bundle
- SHA-256 checksum file

Docker runtime images remain supported, but they are handled as a separate distribution path rather than GitHub Release assets.

CI now builds the PHAR and uploads it as a workflow artifact. A dedicated tag-driven release workflow now publishes GitHub Releases for semver tags.

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

The release payload is:
- `scip-laravel-<version>.phar`
- `scip-laravel-<version>-standalone.tar.gz`
- `scip-laravel-<version>-sha256.txt`
- release notes extracted from the matching changelog section

Docker images remain important, but they are positioned as the reliable fallback for CI and other isolated execution environments.

## Tag and changelog contract

Releases are published from semver git tags such as:
- `v1.2.3`
- `v1.2.3-rc.1`

Before tagging a release:
1. update `ScipLaravel\Cli\ApplicationFactory::VERSION`
2. add a matching `## [<version>]` section to `CHANGELOG.md`
3. create and push the git tag

The release workflow validates that the tag version matches the application version exactly.

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

### Build all GitHub Release assets

```bash
bash tools/release/build-release-artifacts.sh
```

This produces:
- versioned PHAR
- versioned standalone tarball
- versioned SHA-256 checksum file

### Publish a GitHub Release locally

```bash
bash tools/release/publish-github-release.sh v1.2.3
```

This requires GitHub CLI authentication and a matching `CHANGELOG.md` version section.

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

### Run all GitHub Release asset smoke tests

```bash
bash tools/release/smoke-test-release-artifacts.sh
```

This validates the built PHAR, the standalone tarball, and the generated checksum file.

## GitHub Release workflow

The repository includes `.github/workflows/release.yml`.

On push of a semver tag matching `v*`, the workflow:
1. installs dependencies
2. runs `composer check`
3. validates that the git tag matches `ApplicationFactory::VERSION`
4. builds the versioned release assets
5. smoke-tests the PHAR and standalone bundle
6. creates the GitHub Release with assets and changelog-derived release notes
