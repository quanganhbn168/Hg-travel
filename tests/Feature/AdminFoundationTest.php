<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('Đăng nhập quản trị');
    }

    public function test_seeded_admin_can_open_dashboard(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite PDO driver is not available in this environment.');
        }

        $this->seed([PermissionSeeder::class, RoleSeeder::class, AdminUserSeeder::class]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('Booking gần đây');
    }
}
