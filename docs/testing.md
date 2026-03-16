# Testing Strategy

The test harness is intentionally inspired by the strongest parts of the original `scip-php` suite.

That means this project favors:
- focused unit tests for deterministic building blocks
- small integration tests over controlled fixtures
- golden snapshot comparisons for regression safety
- a dedicated downstream-consumer layer for Dora later on

## Current test layers

### Unit

Unit tests cover foundational pieces that later indexing work will build on:
- PHP parser wrapper behavior
- symbol naming conventions
- Composer project metadata discovery

### Integration

Integration tests exercise fixture-level workflows instead of isolated functions.

Right now this is represented by a reusable fixture harness that:
- loads a fixture project
- reads its Composer metadata
- parses its PHP files
- produces a deterministic manifest summary

This is intentionally a stepping stone toward full fixture indexing.

### Snapshot

Snapshot tests already follow the same general pattern we want long-term:
- generate deterministic output from a fixture
- compare it to a committed golden snapshot file

At the moment the snapshot target is a fixture manifest, not a real SCIP snapshot yet. That is deliberate. The important part in Phase 1 is locking in the harness shape, not pretending the full indexer already exists.

### Dora integration

`tests/DoraIntegration` exists now as the reserved location for downstream Dora validation.

Those tests are intentionally deferred until the project produces meaningful SCIP output.

## Directory layout

- `tests/Unit`
- `tests/Integration`
- `tests/Snapshot`
- `tests/DoraIntegration`
- `tests/Support`

## Fixtures and test data

We use two kinds of inputs:
- repository-level fixtures under `fixtures/` for integration-oriented scenarios
- test-local `testdata/` directories under `tests/` for narrow edge cases

That split is borrowed directly from the spirit of the original `scip-php` suite.

## Local workflows

- `composer test`
- `composer test-unit`
- `composer test-integration`
- `composer test-snapshot`
- `composer check`
- `composer check-docker-8.4`
- `composer check-docker-8.5`
- `composer check-matrix`

## Cross-version validation

We now use Docker as the canonical local way to validate both supported PHP runtimes.

That matters because later phases should not be considered done unless the project actually passes on both PHP 8.4 and PHP 8.5, not just the host PHP version.

The Docker matrix runner builds a clean image for each PHP version and executes:
- `composer install`
- `composer check`
- `composer audit --locked --no-interaction`

## Runtime decoupling checks

We also verify that indexing works against temporary project copies outside the repository root.

That guards against accidental assumptions like:
- runtime resources being resolved relative to the indexed project
- builtin stubs being looked up from the target project's vendor tree
- test snapshots depending on machine-specific absolute paths

## Phase 1 and Phase 2 outcome

Phase 1 established the harness we build on in later phases:
- reusable fixture helpers
- parser edge-case coverage
- symbol naming coverage
- Composer project discovery coverage
- snapshot infrastructure with committed golden files

Phase 2 adds the first real indexing layer on top of that harness:
- AST traversal
- basic symbol definitions and references
- minimal type-resolution scaffolding
- deterministic index output for the plain PHP fixture

Phase 4 extends the parser-focused safety net with:
- PHPDoc parsing tests using `phpstan/phpdoc-parser` v2 setup
- builtin symbol resolution tests backed by `jetbrains/phpstorm-stubs`
- regression checks for modern builtin symbols such as `request_parse_body`, `Dom\\HTMLDocument`, and `RoundingMode`

Phase 5 adds runtime-decoupling checks with:
- tool-runtime path resolution independent from indexed projects
- external project indexing tests against temporary fixture copies
- Docker-backed execution checks that validate clean isolated environments

Phase 6 adds Laravel-oriented fixture coverage with:
- Laravel 12 and Laravel 13 sample projects under `fixtures/`
- project-model detection for framework type and Laravel version
- regression checks for key Laravel directories and files
- integration checks proving Laravel fixtures can already pass through the current indexing pipeline

Phase 7 adds Laravel route-analysis coverage with:
- explicit controller array syntax route linking
- invokable controller route linking
- controller-string route linking
- `Route::controller(...)->group(...)` method linking
- `Route::resource`, `Route::apiResource`, and `Route::resources` convention expansion
- route-document snapshots for supported forms

Phase 8 adds provider and container coverage with:
- bootstrap provider registration detection
- `$bindings` and `$singletons` property extraction
- `$this->app->bind|singleton|scoped(...)` extraction
- `App::bind|singleton(...)` extraction
- reusable provider binding map construction
- provider-document snapshots and integration checks
