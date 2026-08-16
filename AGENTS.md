# HG TRIP admin implementation rules

## Admin index contract

- Every table-based view under `resources/views/admin/**/index.blade.php` must use `<x-admin.index-card>`.
- Do not create a new admin list shell with a manually composed `index-header`, `filter-panel`, and `table-card`.
- Before changing an admin list, inspect `resources/views/components/admin/index-card.blade.php`, `bulk-toolbar.blade.php`, and `public/js/admin.js`.
- Resource capabilities are registered only in `App\Support\AdminIndexRegistry`. Do not add a second resource/action/reorder map to a service or controller.
- `BulkActionService`, `ReorderService`, their Form Requests, and the index component must read that registry.
- A resource row must expose `data-record-id`; selectable rows use the registry-derived bulk form ID and `data-check-item`.
- Only resources with an `order_column` in the registry may expose drag-and-drop ordering. Filtered lists must not reorder unless the registry explicitly allows it.
- Workflow resources such as booking must register explicit `status_updates`; do not force them through the generic `is_active` bulk action.
- Media library is the one intentional exception because it is a file grid and upload workflow, not a table index.

## Verification checklist

After changing an admin index:

1. Run `php artisan view:cache`.
2. Render the affected index with real paginator data.
3. Run `php artisan test`.
4. Run `git diff --check`.

The contract test in `tests/Feature/AdminIndexContractTest.php` protects these rules from being silently regressed.
