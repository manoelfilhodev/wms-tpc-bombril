# PHPStan Baseline Reduction Plan

The current `phpstan-baseline.neon` exists to make Larastan level 6 enforceable in CI without mixing historical type debt with new regressions.

Do not try to clear the entire baseline in one pass. Reduce it by module, with tests running after each slice.

## Priority Order

1. Auth and session flows
2. RBAC, permissions and admin middleware
3. Security services, audit logs and security alerts
4. API controllers and API response traits
5. Upload and file-processing paths
6. Operational modules by business risk: demandas, recebimento, kits, etiquetas, relatorios
7. Exports, reports, legacy helpers and old routes

## Working Method

- Pick one module.
- Remove only that module's matching baseline entries.
- Fix real type issues in the module.
- Run focused tests, full tests and PHPStan.
- Commit each module separately.

## Acceptance Criteria Per Module

- No PHPStan errors outside the baseline.
- No new baseline entries.
- Module tests pass.
- Any discovered runtime bug gets fixed or documented before moving to the next module.

