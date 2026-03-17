# Changelog

All notable changes to this project should be documented in this file.

The format is based on Keep a Changelog and the project uses a SemVer-style versioning strategy.

## [Unreleased]

### Added
- Native PHAR build script, PHAR smoke tests, release-artifact build orchestration, and CI PHAR artifact upload.
- GitHub Release publication workflow with semver tag validation, checksum generation, and changelog-derived release notes.
- Docker runtime image packaging.
- Standalone tarball bundle packaging.
- Release artifact smoke-test scripts.

### Changed
- Release asset orchestration now targets the GitHub Release payload directly: PHAR, standalone tarball, and SHA-256 checksums.
- README, testing documentation, and release documentation now describe the PHAR build flow, release publication workflow, and the canonical distribution order.
