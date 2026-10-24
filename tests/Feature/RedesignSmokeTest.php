<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedesignSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        foreach (['/', '/login', '/user/login', '/register', '/user/register'] as $url) {
            $this->assertLessThan(500, $this->get($url)->status(), "Failed: {$url}");
        }
    }

    public function test_admin_pages_do_not_error(): void
    {
        $admin = Admin::factory()->create();

        $urls = [
            '/app/dashboard',
            '/app/profilasnaf',
            '/app/history',
            '/app/viewprofile',
            '/app/household',
            '/app/household/search',
            '/app/hadkifayah',
            '/app/hadkifayah/settings',
            '/app/ai-assessment',
            '/app/ai',
            '/app/importcsv',
            '/app/import-data',
            '/app/import-pdf',
            '/app/import-pdf/history',
            '/app/reports',
            '/app/settings',
            '/app/profile',
            '/app/admin-invitations',
        ];

        foreach ($urls as $url) {
            $res = $this->actingAs($admin, 'web')->get($url);
            $this->assertLessThan(500, $res->status(), "Admin page errored: {$url} ({$res->status()})");
        }
    }

    public function test_portal_pages_do_not_error(): void
    {
        $user = PortalUser::factory()->create(['status' => 'active']);

        $urls = [
            '/user/dashboard',
            '/user/profile',
            '/user/hadkifayah',
            '/user/applications',
            '/user/applications/create',
            '/user/applications/status',
            '/user/applications/scan',
            '/user/household',
            '/user/household/finance',
            '/user/household/assistance',
            '/user/documents',
            '/user/letters',
            '/user/notifications',
            '/user/ai',
        ];

        foreach ($urls as $url) {
            $res = $this->actingAs($user, 'portal')->get($url);
            $this->assertLessThan(500, $res->status(), "Portal page errored: {$url} ({$res->status()})");
        }
    }
}
