# Strict Types Plan — synmod-queue

Same pattern as `packages/synapse` (done — see that package's `phpstan.neon`, now level 6, 0 errors, 503 tests green). Not implemented here yet — inventory only.

## Scope

- 16 in-repo PHP files, 6 missing `declare(strict_types=1)` — `tests/Feature` (1), `routes/*.php` (2), `lang/en/*.php` (3). No `src/` files in the missing list.
- Has its own `phpstan.neon` at **level 5**. Per `CLAUDE.md`, run Pint/PHPStan/Pest from the **main Laravel project root**; bump this package's own `phpstan.neon` `level: 5` → `level: 6` to match synapse's depth.
- Smallest, lowest-risk module in this batch alongside synmod-fm — purely mechanical, no service/model concentration.

## Steps (mirror the synapse execution)

1. Bump `packages/synmod-queue/phpstan.neon` `level: 5` → `level: 6`.
2. Add `declare(strict_types=1);` to the 6 files above.
3. `php -l` sweep + run this module's Pest suite from main root.
4. `vendor/bin/phpstan analyse packages/synmod-queue --error-format=raw --no-progress` from main root; fix findings.
5. `vendor/bin/pint packages/synmod-queue --format agent` from main root.
6. Re-run Pest to confirm green.

## Out of scope for this doc

Actually applying the fixes — inventory only.
