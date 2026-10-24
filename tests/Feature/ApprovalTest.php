<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ProfilAsnaf;
use App\Services\ApprovalService;
use Database\Seeders\HadKifayahSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HadKifayahSettingSeeder::class);
    }

    public function test_approve_generates_pdf_and_updates_profil(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $profil = ProfilAsnaf::create([
            'nama' => 'Ali Bin Abu',
            'ic' => '900101011234',
            'hubungan' => 'Ketua Keluarga',
            'pendapatan' => 1200,
            'status_kelayakan' => 'Layak',
        ]);

        $service = app(ApprovalService::class);
        $result = $service->approve($profil, $admin->id);

        $this->assertStringContainsString('MAIJ-APP-approved-900101011234', $result['letter_path']);
        $this->assertNotEmpty($result['letter_path']);

        $profil->refresh();
        $this->assertEquals('approved', $profil->approval_status);
        $this->assertEquals($admin->id, $profil->approved_by);
        $this->assertNotNull($profil->approved_at);
        $this->assertEquals('approved', $profil->letter_type);
        $this->assertNotNull($profil->letter_generated_at);
    }

    public function test_reject_generates_pdf_with_remarks(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $profil = ProfilAsnaf::create([
            'nama' => 'Siti Binti Ahmad',
            'ic' => '900202012345',
            'hubungan' => 'Isteri',
            'pendapatan' => 2500,
            'status_kelayakan' => 'Tidak Layak',
        ]);

        $service = app(ApprovalService::class);
        $result = $service->reject($profil, 'Pendapatan melebihi Had Kifayah', $admin->id);

        $this->assertStringContainsString('MAIJ-APP-rejected-900202012345', $result['letter_path']);

        $profil->refresh();
        $this->assertEquals('rejected', $profil->approval_status);
        $this->assertEquals('Pendapatan melebihi Had Kifayah', $profil->approval_remarks);
        $this->assertEquals('rejected', $profil->letter_type);
    }

    public function test_approval_updates_timestamps_even_without_email(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $profil = ProfilAsnaf::create([
            'nama' => 'No Email', 'ic' => '333333333333', 'hubungan' => 'Ketua Keluarga', 'pendapatan' => 1000,
        ]);

        $service = app(ApprovalService::class);
        $result = $service->approve($profil, $admin->id);

        $this->assertFalse($result['email_sent']);

        $profil->refresh();
        $this->assertEquals('approved', $profil->approval_status);
        $this->assertNull($profil->letter_sent_at);
    }

    public function test_approve_sets_status_kelayakan_to_layak(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $profil = ProfilAsnaf::create([
            'nama' => 'Dalam Semakan Lama', 'ic' => '444444444444', 'hubungan' => 'Ketua Keluarga',
            'pendapatan' => 1000, 'status_kelayakan' => 'Dalam Semakan',
        ]);

        $service = app(ApprovalService::class);
        $service->approve($profil, $admin->id);

        $profil->refresh();
        $this->assertEquals('Layak', $profil->status_kelayakan);
    }

    public function test_reject_sets_status_kelayakan_to_tidak_layak(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'web');

        $profil = ProfilAsnaf::create([
            'nama' => 'Layak Sebelum Ditolak', 'ic' => '555555555555', 'hubungan' => 'Ketua Keluarga',
            'pendapatan' => 1000, 'status_kelayakan' => 'Layak',
        ]);

        $service = app(ApprovalService::class);
        $service->reject($profil, 'Pendapatan melebihi Had Kifayah', $admin->id);

        $profil->refresh();
        $this->assertEquals('Tidak Layak', $profil->status_kelayakan);
    }
}
