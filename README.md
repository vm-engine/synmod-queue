# Queue Management Module

A backend admin tool for operating Laravel's queue system within a Synapse project — no custom database tables, just a permission-gated UI over Laravel's own `failed_jobs`/`jobs` tables and `queue:*` artisan commands.

## Overview

This module provides:

- Modular architecture with proper namespace organization
- Livewire v4 MFC (Multi-File Component) patterns
- ACL (Access Control List) implementation with three independent permissions (failed / pending / worker)
- Backend navigation with a single parent menu and three sub-items
- Direct wrapping of `queue:retry`, `queue:forget`, `queue:flush`, `queue:clear`, and `queue:restart`
- Testing with Pest

## Installation

### 1. Add Repository URL

Add the repository to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/vm-engine/synmod-queue"
        }
    ]
}
```

### 2. Install Package

```bash
composer require vm-engine/synmod-queue
```

### 3. Discover Modules and Cache Configuration

```bash
php artisan synapps:discover
php artisan synapps:config
```

### 4. Build Assets

```bash
npm run build
```

> No `php artisan migrate` step is required — this module has no migrations.

## Features

- **Failed Job Management**: View, retry (single/bulk), delete (single/bulk), and flush-all failed jobs
- **Pending Job Management**: View and delete pending jobs (database queue driver only), with a clear-all action
- **Worker Control**: View current queue driver and last-restarted timestamp, trigger a graceful `queue:restart`
- **ACL Integration**: Independently gated permissions per page (`queue.failed`, `queue.pending`, `queue.worker`)

## Module Structure

```
packages/synmod-queue/
├── src/
│   └── QueueServiceProvider.php             # Auto-registers components via AutoRegistersComponents
├── resources/views/components/               # MFC Livewire components (Livewire v4)
│   ├── failed-job-list/
│   │   ├── failed-job-list.php
│   │   └── failed-job-list.blade.php
│   ├── pending-job-list/
│   │   ├── pending-job-list.php
│   │   └── pending-job-list.blade.php
│   └── worker-control/
│       ├── worker-control.php
│       └── worker-control.blade.php
├── routes/
│   ├── web.backend.php
│   └── web.php
├── lang/en/
├── tests/
├── module.json
└── composer.json
```

> This module uses the MFC (Multi-File Component) format. Components live in `resources/views/components/` rather than `src/Livewire/`.

## Namespace Structure

This module uses the **VmEngine vendor namespace**:

- Application: `VmEngine\Queue\`
- Factories: `VmEngine\Queue\Factories\` (declared for convention parity; unused)
- Seeders: `VmEngine\Queue\Seeders\` (declared for convention parity; unused)

## Permissions

The module defines the following permissions in `module.json`, one per admin page:

### Failed Jobs (`queue.failed`)
- Read, Update, Delete (no Create)

### Pending Jobs (`queue.pending`)
- Read, Delete (no Create, no Update)

### Worker (`queue.worker`)
- Read, Update (no Create, no Delete)

## Routes

All backend routes are automatically prefixed with `/admin/queue/` and require authentication:

- `GET /admin/queue/failed` - Failed job list
- `GET /admin/queue/pending` - Pending job list
- `GET /admin/queue/worker` - Worker control

## Development

### Running Tests

**IMPORTANT:** Tests for this module **must be run from the parent Laravel project**, not from within the module directory itself.

From the parent Laravel project root:

```bash
# Run this module's tests (use packages path for local development)
php vendor/bin/pest packages/synmod-queue/tests

# Run specific test
php vendor/bin/pest --filter="can retry a failed job"
```

### Code Quality

```bash
# Fix code style (from project root)
php vendor/bin/pint packages/synmod-queue/

# PHPStan analysis (from project root)
php vendor/bin/phpstan analyse --configuration=packages/synmod-queue/phpstan.neon
```

## Key Patterns

### MFC Component Format

Components use Livewire v4's Multi-File Component format with anonymous classes:

```php
// resources/views/components/failed-job-list/failed-job-list.php
<?php

use Livewire\Component;

new class extends Component {
    // component logic

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
```

```blade
{{-- resources/views/components/failed-job-list/failed-job-list.blade.php --}}
<div>
    {{-- component view --}}
</div>
```

### Namespace Notation

All Livewire component references use namespace notation:

```blade
{{-- In Blade views --}}
<livewire:queue::failed-job-list />
<livewire:queue::pending-job-list />
<livewire:queue::worker-control />
```

```php
// In route files
Route::livewire('/failed', 'queue::failed-job-list');
```

### Component Auto-Registration

Components are auto-registered via the `AutoRegistersComponents` trait — no manual registration needed:

```php
class QueueServiceProvider extends ServiceProvider
{
    use AutoRegistersComponents;

    public function register(): void
    {
        $this->registerComponents();
    }
}
```

### Database-Driver-Only Guard

Pending job management only works when `config('queue.default')` resolves to the `database` driver. Other drivers show `labels.pending_driver_warning` instead of a job list.

## Requirements

- Laravel 12
- Livewire v4
- vm-engine/synapse ^3.0 (views use the per-page selector, semantic CSS classes and the CSP-safe Alpine build)
- vm-engine/synapps-auth ^2.0

## License

This module is part of the Synapse Engine package suite.

## Author

**Achmad Ardiansyah**
Email: theadods@gmail.com

## Version

0.1.0
