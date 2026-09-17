# Changelog

All notable changes to `vm-engine/synmod-queue` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **Hardened typing to pass PHPStan level 7 clean.** `FailedJobService::list()` and `PendingJobService::list()` now declare `@return LengthAwarePaginator<int, \stdClass>` and their row-transform closures use `\stdClass` explicitly (DB query-builder rows), erasing the "undefined property" noise; the private `log()` helpers in all three services accept `int|string|null` and coerce with `(int)` before calling `SynAuth::logActivity()` (the `auth()->id()` return can be a string id).
- **Renamed `$limit` to `$perPage` on `FailedJobList`/`PendingJobList`** (including the `#[Url]` query-string key and `rememberedParams()` entry) and adopted `<x-synapse-per-page-selector>` in place of the hand-rolled per-page `<select>`. Page-size options now standardize to the shared component's fixed 10/15/25/50 (was 15/25/50/100 on both pages — "10" is newly available, "100" is gone). **Behavior change:** old bookmarked `?limit=` URLs are silently ignored after this and fall back to the default (15). See `.claude/plans/2026-09-16-per-page-selector-unification.md`.
- **Semantic CSS migration** across all 3 Blade views (`pending-job-list`, `failed-job-list`, `worker-control`) — inline Tailwind utility soup replaced with `vm-engine/synapse`'s `<x-synapse-panel>`, `<x-synapse-alert>`, and `<x-synapse-search-box>` components, plus its `.syn-selection-bar`/`.syn-per-page-selector`/`.syn-per-page-select` semantic classes. No visual redesign, no behavior change — see `.claude/plans/2026-09-16-synmod-queue-semantic-css.md`.
- Widened `vm-engine/synapse` dependency constraint to `^2.1|^3.0` for Synapse 3.0 compatibility.
- Raised this package's PHPStan level from 5 to 7.

## [0.1.0] - 2026-07-31

### Added
- Backend navigation with a single parent menu ("Queue") and three sub-items (Failed Jobs, Pending Jobs, Worker)
- Split ACL permissions: `queue.failed` (read/update/delete), `queue.pending` (read/delete), `queue.worker` (read/update)
- Failed Jobs page: search, sort, single/bulk retry and delete, flush-all, backed by `FailedJobService` (wraps `queue:retry`/`queue:forget`/`queue:flush`)
- Pending Jobs page: search, queue-name filter, single/bulk delete, per-queue clear, backed by `PendingJobService` — restricted to the `database` queue driver, with a guard enforced both in the UI and at the service layer
- Worker page: shows current queue driver and last-restart timestamp, triggers a graceful `queue:restart` with an explanatory note that a process supervisor (Supervisor/systemd) is required to relaunch the worker
- Worker restart is OTP-protected via `vm-engine/synapps-auth`'s `WithOtpProtection` trait — on top of the `queue.worker.update` ACL check and confirm-dialog, the user must verify a one-time code before the restart actually runs (requires `synapps-auth` ^2.2)
- Activity logging for every mutating action via `SynAuth::logActivity()`
- Translation files for ACL labels, menu labels, and UI copy
- Package documentation (README, CLAUDE.md, release checklist)
- Full Feature test suite (32 tests) covering ACL gating, list/search/filter behavior, single and bulk actions, the non-database-driver guard, and OTP-verification gating on worker restart

[Unreleased]: https://repo.4visionmedia.com/vm-engine/synmod-queue/compare/v0.1.0...HEAD
[0.1.0]: https://repo.4visionmedia.com/vm-engine/synmod-queue/releases/tag/v0.1.0
