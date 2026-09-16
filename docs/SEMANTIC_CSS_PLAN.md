# Semantic CSS Plan — synmod-queue

**Status: done.** Implemented per `.claude/plans/2026-09-16-synmod-queue-semantic-css.md`, using the same tier-0/1/2 method as `packages/synapse` and `packages/synapps-auth`'s completed passes — not the `syn-queue-*`/dedicated-file convention originally proposed below (superseded once `packages/synapse`'s existing Blade components turned out to already cover most of this module's markup).

## What actually happened

- `pending-job-list.blade.php`, `failed-job-list.blade.php`, `worker-control.blade.php` — panel wrappers replaced with `<x-synapse-panel>` (`toolbar` slot for header actions), colored notice boxes replaced with `<x-synapse-alert>`, search inputs replaced with `<x-synapse-search-box>`.
- One new shared class added to `packages/synapse`'s `synapse-components.css` (not a module-specific file): `.syn-selection-bar` — the bulk-action bar shape recurred identically in both list views and matched nothing existing.
- Per-page selectors kept their existing 15/25/50/100 option set (differs from `<x-synapse-per-page-selector>`'s fixed 10/15/25/50) — only the CSS classes (`.syn-per-page-selector`/`.syn-per-page-select`) were reused, not the full component, to avoid changing available page sizes.
- Sort icons and other FontAwesome icons were left untouched — swapping to `<x-synapse-sort-icon>` would change the icon set to Phosphor, a visual change out of scope for this pass (tracked separately, see `.ai/rules/resources-views.md`'s FontAwesome→Phosphor note).

## Original inventory (for history)

| File | `class=` occurrences (before) |
| --- | --- |
| `resources/views/components/pending-job-list/pending-job-list.blade.php` | 38 |
| `resources/views/components/failed-job-list/failed-job-list.blade.php` | 37 |
| `resources/views/components/worker-control/worker-control.blade.php` | 16 |
