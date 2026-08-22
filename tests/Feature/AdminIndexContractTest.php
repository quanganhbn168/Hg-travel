<?php

namespace Tests\Feature;

use App\Support\AdminIndexRegistry;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminIndexContractTest extends TestCase
{
    #[Test]
    public function table_index_views_use_the_shared_admin_index_component(): void
    {
        $files = glob(resource_path('views/admin/*/index.blade.php')) ?: [];

        foreach ($files as $file) {
            if (str_ends_with(str_replace('\\', '/', $file), '/media/index.blade.php')) {
                continue;
            }

            $this->assertStringContainsString(
                '<x-admin.index-card',
                file_get_contents($file),
                "Admin index must use the shared component: {$file}",
            );
        }
    }

    #[Test]
    public function every_view_resource_is_registered_once(): void
    {
        $registered = AdminIndexRegistry::resources();
        $files = glob(resource_path('views/admin/*/index.blade.php')) ?: [];
        $resources = [];

        foreach ($files as $file) {
            preg_match_all('/\bresource="([^"]+)"/', file_get_contents($file), $matches);
            $resources = [...$resources, ...$matches[1]];
        }

        $this->assertNotEmpty($resources);
        $this->assertEmpty(array_diff($resources, $registered));
        $this->assertSameSize(array_unique($resources), $resources);
    }

    #[Test]
    public function reorderable_resources_have_a_manual_order_column(): void
    {
        foreach (AdminIndexRegistry::reorderResources() as $resource) {
            $this->assertNotNull(AdminIndexRegistry::orderColumnFor($resource));
            $this->assertNotSame('', AdminIndexRegistry::tableFor($resource));
        }
    }

    #[Test]
    public function index_views_do_not_duplicate_registry_capabilities(): void
    {
        $files = glob(resource_path('views/admin/*/index.blade.php')) ?: [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertStringNotContainsString(':bulk-actions=', $contents, $file);
            $this->assertStringNotContainsString('bulk-delete-warning=', $contents, $file);
            $this->assertStringNotContainsString(':reorder-enabled=', $contents, $file);
        }

        $this->assertStringNotContainsString('const RESOURCES', file_get_contents(app_path('Services/BulkActionService.php')));
        $this->assertStringNotContainsString('const RESOURCES', file_get_contents(app_path('Services/ReorderService.php')));
    }

    #[Test]
    public function sidebar_items_have_real_routes_and_no_placeholder_links(): void
    {
        foreach (config('sidebar.menu', []) as $item) {
            if (($item['type'] ?? 'link') === 'header') {
                continue;
            }

            $this->assertTrue(
                Route::has($item['route'] ?? ''),
                "Sidebar item must use a registered route: {$item['label']}",
            );
        }

        $this->assertStringNotContainsString('href="#"', file_get_contents(resource_path('views/admin/partials/sidebar.blade.php')));
    }
}
