<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Đăng nhập quản trị');
    }

    public function test_seeded_admin_can_open_dashboard(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite PDO driver is not available in this environment.');
        }

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Booking gần đây');
    }
}
