# Changelog

All notable changes to `vm-engine/synmod-queue` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-07-31

### Added
- Backend navigation with a single parent menu ("Queue") and three sub-items (Failed Jobs, Pending Jobs, Worker)
- Split ACL permissions: `queue.failed` (read/update/delete), `queue.pending` (read/delete), `queue.worker` (read/update)
- Failed Jobs page: search, sort, single/bulk retry and delete, flush-all, backed by `FailedJobService` (wraps `queue:retry`/`queue:forget`/`queue:flush`)
- Pending Jobs page: search, queue-name filter, single/bulk delete, per-queue clear, backed by `PendingJobService` — restricted to the `database` queue driver, with a guard enforced both in the UI and at the service layer
- Worker page: shows current queue driver and last-restart timestamp, triggers a graceful `queue:restart` with an explanatory note that a process supervisor (Supervisor/systemd) is required to relaunch the worker
- Activity logging for every mutating action via `SynAuth::logActivity()`
- Translation files for ACL labels, menu labels, and UI copy
- Package documentation (README, CLAUDE.md, release checklist)
- Full Feature test suite (28 tests) covering ACL gating, list/search/filter behavior, single and bulk actions, and the non-database-driver guard

[Unreleased]: https://repo.4visionmedia.com/vm-engine/synmod-queue/compare/v0.1.0...HEAD
[0.1.0]: https://repo.4visionmedia.com/vm-engine/synmod-queue/releases/tag/v0.1.0
