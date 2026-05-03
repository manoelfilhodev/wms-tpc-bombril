# Migration Baseline Strategy

## Goal
Ensure `php artisan migrate` runs safely on:
- Existing environments with legacy data and previously imported schema
- New installations with clean databases

## Approach
1. Keep both migration tracks (Laravel defaults + imported legacy files) to avoid breaking history.
2. Make duplicated infrastructure migrations idempotent with `Schema::hasTable(...)` guards.
3. Make legacy foreign key migrations defensive when historical orphan rows exist:
   - add FK only when referenced data is consistent
   - skip FK creation when consistency is not guaranteed
4. Make additive schema migrations idempotent using `Schema::hasColumn(...)` checks.

## Notes
- This baseline prioritizes deployment safety and migration continuity.
- Data-cleanup (orphan normalization) can be done in a separate controlled maintenance task.
