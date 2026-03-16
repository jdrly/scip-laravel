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

## Phase 1 outcome

Phase 1 establishes the harness we will build on in later phases:
- reusable fixture helpers
- parser edge-case coverage
- symbol naming coverage
- Composer project discovery coverage
- snapshot infrastructure with committed golden files
