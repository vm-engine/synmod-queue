# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ⚠️ Git Policy

**NEVER commit/push without explicit user approval.** Always show changes and wait for user confirmation.

## ⚠️ composer.json Versioning

**IMPORTANT for local development and releases:**

- `composer.json` in this package has a `"version"` key for **local development only** (currently: `0.1.0-dev`)
- This version is required for path repository constraint resolution in the main project
- **BEFORE COMMITTING** any changes:
  - If **only the version key changed** → Remove the entire `"version"` line before committing
  - If **other fields changed** along with version → Revert the version key to `"0.1.0-dev"` and keep other changes
- **BEFORE RELEASING**:
  - Remove the `"version"` key entirely from `composer.json`
  - Create a git tag: `git tag v{X.Y.Z}` (e.g., `git tag v0.1.0`)
  - Push the tag: `git push origin v{X.Y.Z}`
  - Composer will read the version from the git tag automatically

**Workflow:**
```bash
# During development: version key is present (0.1.0-dev)
# Before committing: remove or revert version key
# Before releasing: no version key, use git tag instead
```

---

## Project Overview

This is the **Queue Management Module** (`vm-engine/synmod-queue`), a **package module** for Laravel Synapse architecture. It is a backend-only admin tool for operating Laravel's own queue system — it does not introduce any custom database tables. It wraps the framework's `failed_jobs`/`jobs` tables and `queue:*` artisan commands behind a permission-gated admin UI.

**Type:** Composer package module (lives in `packages/synmod-queue/`)
**Package Name:** `vm-engine/synmod-queue`
**Module ID:** `queue`
**URL Prefix:** `/admin/queue/*` (backend-only, no frontend routes)

## Architecture

### No Custom Tables

Unlike most Synapse modules, this module has **no migrations**. It reads and writes directly to Laravel's built-in `failed_jobs` table and (for the database queue driver only) the `jobs` table. There are no Eloquent models of its own for these — they are managed through service classes that wrap Laravel's `queue:*` artisan commands (`queue:retry`, `queue:forget`, `queue:flush`, `queue:clear`, `queue:restart`).

The only models, `Models\PendingJob` and `Models\FailedJob`, exist to issue and validate `WithDeleteToken` delete tokens (single-row deletes never send a raw id/uuid to the browser). Services stamp `delete_token` on each listed row; reading and deleting still go through the query builder / `queue:forget`.

### Namespace Structure

This is a **package module** that uses the **VmEngine vendor namespace** (not SynApps):

- **Application:** `VmEngine\Queue\` → `src/`
- **Factories:** `VmEngine\Queue\Factories\` → `database/factories/` (declared for convention parity; unused — no models to factory)
- **Seeders:** `VmEngine\Queue\Seeders\` → `database/seeders/` (declared for convention parity; unused — no tables to seed)

These namespaces are defined in `composer.json`'s `autoload.psr-4` block.

### Service Provider Registration

Livewire components are auto-registered via the `AutoRegistersComponents` trait using Livewire v4's `addNamespace()`:

```php
// In QueueServiceProvider::register()
$this->registerComponents(); // auto-discovers all Livewire components
```

Components are referenced with namespace notation:
```blade
<livewire:queue::failed-job-list />
<livewire:queue::pending-job-list />
<livewire:queue::worker-control />
```

### ACL (Access Control) — Split by Concern

Unlike the single hierarchical `manage` permission used in some modules, this module splits ACL into three independent top-level permissions, one per admin page, so an operator can be granted (for example) failed-job visibility without also getting worker-restart rights:

- `queue.failed` — read/update/delete (no create) — view, retry, and delete failed jobs
- `queue.pending` — read/delete only (no create, no update) — view and delete pending jobs (database driver only)
- `queue.worker` — read/update (no create, no delete) — view worker status and trigger a restart

Routes use `can-access` middleware per page:
```php
->middleware('can-access:queue.failed')
->middleware('can-access:queue.pending')
->middleware('can-access:queue.worker')
```

### Navigation Structure

Defined in `module.json`:
- **Backend nav:** Single parent menu ("Queue") with three sub-items (Failed Jobs, Pending Jobs, Worker), each gated by its own ACL key
- Uses translation keys: `__queue::menu.be.parent`, `__queue::menu.be.failed`, `__queue::menu.be.pending`, `__queue::menu.be.worker`
- Icon: Font Awesome classes (`fa-solid fa-list-check` for parent)

### Route Organization

- `routes/web.backend.php` → Backend admin routes (auto-prefixed `/admin/queue/*`, auto-authenticated), one `Route::livewire()` call per page
- `routes/web.php` → Empty file, **required for Synapse compatibility** — `SynapseRouteServiceProvider` iterates every module's `routes/web.php` and expects the file to exist even if this module has no non-backend routes (same convention documented in `packages/synmod-fm/CLAUDE.md`'s "Known Behaviours" section)

### Livewire Components (MFC pattern)

Components live under `resources/views/components/` using the MFC (Multi-File Component) pattern — logic in `.php`, template in `.blade.php`, same folder name:

- `failed-job-list/` — Lists `failed_jobs`, supports retry/delete single + bulk, flush-all
- `pending-job-list/` — Lists pending `jobs` (database driver only — shows a driver warning otherwise), supports delete single + bulk, clear-all
- `worker-control/` — Shows current queue driver + last-restarted timestamp, triggers `queue:restart`

### Database-Driver-Only Guard

Pending job management only works with the `database` queue driver (Laravel has no generic "list pending jobs" API for other drivers like Redis/SQS). The `pending-job-list` component must detect the active driver and show `labels.pending_driver_warning` instead of a job list when the driver isn't `database`.

### Worker Restart Semantics

`queue:restart` does not itself kill running workers — it sets a cache timestamp that running `queue:work` processes check between jobs and exit gracefully on. **A process supervisor (Supervisor, systemd, Horizon, etc.) must be configured to auto-relaunch the worker process** after it exits, or the queue will stop processing entirely. The worker-control UI must make this explicit via `labels.supervisor_note`.

### Worker Restart is OTP-Protected

Restarting workers requires the requesting user to re-verify their identity via a one-time code, on top of the `queue.worker.update` ACL check and the confirm-dialog. Implemented via `vm-engine/synapps-auth`'s `VmEngine\SynAuth\Traits\WithOtpProtection` (requires `synapps-auth` ^2.2, when this trait/service was introduced — see its own `CHANGELOG.md`), not a bespoke OTP flow:

- `worker-control.php`'s `restart()` (the confirm-dialog callback) checks ACL, closes the confirm-dialog (`synapse-confirmed`), then calls `$this->requireOtp('queue.restart-workers')` — this dispatches `request-otp-verification`, which `<livewire:synauth-otp-verify-action />` (rendered once on the page, outside modals) listens for and shows its own code-entry modal.
- `#[On('otp-verified')] handleOtpVerified(string $token, string $purpose)` fires once the user enters a valid code. It checks `$purpose` matches `queue.restart-workers`, calls `$this->validateOtpToken($token)` (single-use, tied to the authenticated user), re-checks ACL defensively, then performs the actual `WorkerService::restart()`.
- Tests generate a real token directly via `app(OtpProtectionService::class)->generateActionToken($user, 'queue.restart-workers')` and call `handleOtpVerified()` directly — no need to drive the full `OtpVerifyAction` modal component in Feature tests (see `WithOtpProtectionTraitTest.php` in `synapps-auth` for the same convention).

### Localization

Translation files in `lang/en/`:
- `acl.php` → Permission labels
- `menu.php` → Navigation labels
- `labels.php` → Page titles, column headers, button labels, confirm-dialog copy, notification messages, worker page copy

Reference in code: `__queue::menu.be.parent`
Reference in views: `@lang('queue::labels.field_name')`

## Development Commands

**Testing (from parent Laravel project root):**
```bash
# Run all tests
./vendor/bin/pest

# Run this module's tests specifically
./vendor/bin/pest packages/synmod-queue/tests

# Filter specific test
./vendor/bin/pest --filter="can retry a failed job"
```

**Code Quality (from parent Laravel project root):**
```bash
# Static analysis (run BEFORE pint)
vendor/bin/phpstan analyse --configuration=packages/synmod-queue/phpstan.neon

# Fix code style
./vendor/bin/pint packages/synmod-queue
```

**Development:**
This module is developed within a parent Laravel Synapse project. Changes here should be tested in the parent project context.

## Testing Notes

- Uses `RefreshDatabase` trait for isolated test database
- ACL testing: Create permissions via `RolePermission::factory()->forModule('queue', 'failed')`, `->forModule('queue', 'pending')`, `->forModule('queue', 'worker')` as needed per test
- Tests authenticate users with roles: `$this->actingAs($user)`
- Route testing uses named routes: `route('backend.queue.failed')`, `route('backend.queue.pending')`, `route('backend.queue.worker')`
- Seed `failed_jobs`/`jobs` rows directly via `DB::table()` inserts (no factories exist for Laravel's own queue tables)

## Key Files

- `module.json` → Module configuration, ACL, navigation, dependencies
- `src/QueueServiceProvider.php` → Livewire component registration
- `src/Services/*` → Service classes wrapping `queue:*` artisan commands and direct table queries
- `composer.json` → Namespace definitions (critical for PSR-4 autoloading)
