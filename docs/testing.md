# Testing Strategy

The initial test harness is intentionally modeled after the strongest parts of the original `scip-php` test suite.

## Layers

- Unit tests for small, deterministic building blocks.
- Integration tests for indexing fixtures end to end.
- Snapshot tests for SCIP output regressions.
- Dora integration tests as downstream consumer validation.

## Phase 0 status

Phase 0 only introduces the baseline test tooling and directory structure. The richer fixture and snapshot coverage will land in later phases.
