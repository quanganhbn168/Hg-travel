# Admin index standard

The admin list UI has one source of truth: `App\Support\AdminIndexRegistry`.

Each registered resource defines its model, table, user-facing label, allowed bulk actions, delete warning, optional status transitions, and optional `order_column` plus filter keys that make drag-and-drop unsafe. Workflow resources such as booking use `status_updates` instead of the generic `is_active` toggle. The following consumers must never maintain their own resource maps:

- `resources/views/components/admin/index-card.blade.php`
- `resources/views/components/admin/bulk-toolbar.blade.php`
- `App\Services\BulkActionService`
- `App\Services\ReorderService`
- `BulkActionRequest` and `ReorderRecordsRequest`

An index view owns only its domain-specific filters, columns, row markup, and paginator. The shared component owns the shell, toolbar, form ID, capability detection, reorder button, and common endpoints.

When adding a new resource:

1. Add one definition to `AdminIndexRegistry`.
2. Use `<x-admin.index-card resource="...">` in the index view.
3. Add `data-record-id` to each data row and use the registry form ID for selection inputs.
4. Add `order_column` only when the default list ordering is a genuine manual order.
5. Run the admin index contract test and the standard verification checklist.
