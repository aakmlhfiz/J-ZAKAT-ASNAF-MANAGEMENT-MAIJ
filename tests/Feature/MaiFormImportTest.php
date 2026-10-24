<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\DocumentImport;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaiFormImportTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'email' => 'maij-'.uniqid().'@maij.gov.my',
            'password' => bcrypt('password'),
        ]);

        try {
            DB::connection('user')->table('users')->delete();
        } catch (\Throwable $e) {
            // Ignore when the portal connection is unavailable.
        }
    }

    private function import(array $overrides = []): DocumentImport
    {
        return DocumentImport::create(array_merge([
            'user_id' => $this->admin->id,
            'original_filename' => 'MAIJ_borang_4pages.pdf',
            'stored_path' => 'ocr/maij.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4096,
            'file_hash' => 'maij_'.uniqid(),
            'document_type' => 'borang_bantuan_maij',
            'status' => 'extracted',
            'page_count' => 4,
            'extracted_data' => [
                'nama' => ['value' => 'Muhammad Akmal Hafiz bin Shamsul Bahare', 'confidence' => 92],
                'ic' => ['value' => '800315015678', 'confidence' => 99],
                'pendapatan' => ['value' => '1500.00', 'confidence' => 90],
            ],
            'ocr_result' => [
                'document' => ['pages' => 4, 'document_type' => 'MAIJ Zakat Assistance Application Form', 'sections_detected' => []],
                'fields' => [
                    'nama' => ['value' => 'Muhammad Akmal Hafiz bin Shamsul Bahare', 'confidence' => 92, 'page' => 1, 'input_type' => 'printed'],
                ],
                'applications' => [
                    ['type' => 'Bantuan Sara Hidup', 'selected' => true, 'amount_requested' => '500.00', 'reason' => 'Gaji tidak mencukupi', 'page' => 1],
                ],
                'dependents' => [
                    ['row' => 1, 'nama_penuh' => 'MUHAMMAD RUSYDI BIN MD RAHFEE', 'no_ic' => '040311084031', 'sekolah_pekerjaan' => 'Kelab', 'pendapatan' => 1800, 'hubungan' => 'Kawan', 'tinggal_bersama' => 'Sendiri', 'needs_review' => true],
                    ['row' => 2, 'nama_penuh' => null, 'no_ic' => null, 'sekolah_pekerjaan' => null, 'pendapatan' => null, 'hubungan' => null, 'tinggal_bersama' => null, 'needs_review' => false],
                ],
                'income' => [],
                'expenses' => [],
                'declaration' => ['pengakuan_nama' => ['value' => 'Muhammad Akmal Hafiz bin Shamsul Bahare', 'confidence' => 92], 'pengakuan_tarikh' => ['value' => '2026-08-12', 'confidence' => 55], 'signature' => ['present' => false, 'confidence' => 55]],
                'verification' => ['pengesah_nama' => ['value' => 'MUHAMMAD DANIAL BIN SHAHARUDDIN', 'confidence' => 91], 'pengesah_jawatan' => ['value' => 'Ketua Kampung', 'confidence' => 95], 'stamp' => ['present' => false, 'confidence' => 55]],
            ],
        ], $overrides));
    }

    public function test_preview_renders_ocr_result_structured_review(): void
    {
        $import = $this->import();

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.preview', $import->id));

        $response->assertStatus(200);
        $response->assertSee('Maklumat Berjaya Dikesan');
        $response->assertSee('MUHAMMAD RUSYDI BIN MD RAHFEE');
        $response->assertSee('Bantuan Sara Hidup');
        $response->assertSee('MUHAMMAD DANIAL BIN SHAHARUDDIN');
    }

    public function test_preview_shows_sections_detected(): void
    {
        $import = $this->import();
        $import->update(['ocr_result' => array_merge($import->ocr_result, [
            'document' => ['pages' => 4, 'document_type' => 'MAIJ Zakat Assistance Application Form', 'sections_detected' => [
                ['number' => '4', 'title' => 'MAKLUMAT PENGENALAN PEMOHON', 'page' => 1],
                ['number' => '11', 'title' => 'PENGAKUAN PEMOHON', 'page' => 4],
            ]],
        ])]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.preview', $import->id));

        $response->assertStatus(200);
        $response->assertSee('§4 · Muka 1');
        $response->assertSee('§11 · Muka 4');
    }

    public function test_commit_creates_profil_household_and_dependents(): void
    {
        $import = $this->import();

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.commit', $import->id), [
                'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
                'ic' => '800315015678',
                'pendapatan' => 1500,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('profil_asnafs', ['ic' => '800315015678', 'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare']);

        $member = HouseholdMember::where('ic', '800315015678')->first();
        $this->assertNotNull($member);

        // Dependents from ocr_result were persisted as household members.
        $this->assertDatabaseHas('household_members', [
            'ic' => '040311084031',
            'nama' => 'MUHAMMAD RUSYDI BIN MD RAHFEE',
        ]);

        $this->assertDatabaseHas('document_imports', [
            'id' => $import->id,
            'status' => 'committed',
        ]);
    }

    public function test_commit_blocks_existing_duplicate_application(): void
    {
        $portalUser = PortalUser::factory()->create([
            'email' => 'portal-'.uniqid().'@maij.gov.my',
            'password' => bcrypt('password'),
        ]);

        Application::create([
            'user_id' => $portalUser->id,
            'ic_no' => '800315015678',
            'full_name' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
            'phone' => '0123456789',
            'email' => $portalUser->email,
            'status' => 'submitted',
        ]);

        $import = $this->import();

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.commit', $import->id), [
                'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
                'ic' => '800315015678',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('profil_asnafs', 0);
        $this->assertDatabaseCount('households', 0);
    }

    public function test_commit_rolls_back_when_household_member_insert_fails(): void
    {
        $import = $this->import();

        // Two dependents sharing the same IC violate the unique constraint on
        // household_members.ic mid-transaction → everything must roll back.
        $import->update(['ocr_result' => array_merge($import->ocr_result, [
            'dependents' => [
                ['row' => 1, 'nama_penuh' => 'ALI BIN ABU', 'no_ic' => '040311084031', 'sekolah_pekerjaan' => null, 'pendapatan' => null, 'hubungan' => null, 'tinggal_bersama' => null, 'needs_review' => true],
                ['row' => 2, 'nama_penuh' => 'AHMAD BIN ABU', 'no_ic' => '040311084031', 'sekolah_pekerjaan' => null, 'pendapatan' => null, 'hubungan' => null, 'tinggal_bersama' => null, 'needs_review' => true],
            ],
        ])]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.commit', $import->id), [
                'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
                'ic' => '800315015678',
                'pendapatan' => 1500,
            ]);

        $this->assertDatabaseCount('profil_asnafs', 0);
        $this->assertDatabaseCount('households', 0);
        $this->assertDatabaseCount('household_members', 0);
        $this->assertTrue($response->isRedirect());
    }

    public function test_commit_requires_force_import_for_existing_ic(): void
    {
        ProfilAsnaf::create([
            'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
            'ic' => '800315015678',
            'pendapatan' => 1500,
        ]);

        $import = $this->import();

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.commit', $import->id), [
                'nama' => 'Muhammad Akmal Hafiz bin Shamsul Bahare',
                'ic' => '800315015678',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('profil_asnafs', 1);
    }
}
