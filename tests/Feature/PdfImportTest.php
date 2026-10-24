<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DocumentImport;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ProfilAsnaf;
use App\Services\ImportDuplicateDetectionService;
use App\Services\ImportHouseholdLookupService;
use App\Services\ImportValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfImportTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'email' => 'admin@maij.gov.my',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function import_pdf_page_is_accessible()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.index'));

        $response->assertStatus(200);
        $response->assertSee('Import PDF');
        $response->assertSee('Muat Naik');
    }

    /** @test */
    public function valid_pdf_can_be_uploaded()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('borang.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.upload'), ['file' => $file]);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_imports', [
            'original_filename' => 'borang.pdf',
            'status' => 'processing',
            'user_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function jpg_image_can_be_uploaded()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->image('borang.jpg');

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.upload'), ['file' => $file]);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_imports', [
            'original_filename' => 'borang.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    /** @test */
    public function png_image_can_be_uploaded()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->image('borang.png');

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.upload'), ['file' => $file]);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_imports', [
            'original_filename' => 'borang.png',
        ]);
    }

    /** @test */
    public function invalid_file_type_is_rejected()
    {
        $file = UploadedFile::fake()->create('document.docx', 100);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.upload'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    /** @test */
    public function duplicate_file_returns_info_message()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('borang.pdf', 100, 'application/pdf');
        $hash = sha1_file($file->getRealPath());

        DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'file_hash' => $hash,
            'status' => 'extracted',
            'page_count' => 1,
        ]);

        Storage::fake('local');
        $file2 = UploadedFile::fake()->create('borang.pdf', 100, 'application/pdf');
        // Copy content to ensure same hash
        copy($file->getRealPath(), $file2->getRealPath());

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.upload'), ['file' => $file]);

        $response->assertSessionHas('info');
    }

    /** @test */
    public function existing_ic_duplicate_is_detected()
    {
        ProfilAsnaf::create([
            'nama' => 'Ahmad Bin Ali',
            'ic' => '800315015678',
            'pendapatan' => 1500,
            'status_kelayakan' => 'Layak',
        ]);

        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'test_hash',
            'status' => 'extracted',
            'page_count' => 1,
            'extracted_data' => [
                'nama' => ['value' => 'Ahmad Bin Ali', 'confidence' => 95],
                'ic' => ['value' => '800315015678', 'confidence' => 99],
            ],
            'overall_confidence' => 97,
        ]);

        $detection = new ImportDuplicateDetectionService;
        $result = $detection->analyze($import->extracted_data);

        $this->assertTrue($result['has_warnings']);
        $this->assertGreaterThan(0, $result['warning_count']);
    }

    /** @test */
    public function existing_household_is_found_by_ic()
    {
        $household = Household::create([
            'household_id' => 'HR-2026-0001',
            'alamat' => 'Johor Bahru',
            'total_income' => 2000,
            'status' => 'active',
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'nama' => 'Ahmad Bin Ali',
            'ic' => '800315015678',
            'hubungan' => 'Ketua Isi Rumah',
            'umur' => 46,
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'nama' => 'Siti Binti Abu',
            'ic' => '820415015234',
            'hubungan' => 'Isteri',
            'umur' => 44,
        ]);

        $service = new ImportHouseholdLookupService;
        $result = $service->lookupByIC('800315015678');

        $this->assertNotNull($result);
        $this->assertTrue($result['found']);
        $this->assertEquals('HR-2026-0001', $result['household_id']);
        $this->assertEquals('Ahmad Bin Ali', $result['head_of_household']);
        $this->assertEquals(2, $result['total_members']);
    }

    /** @test */
    public function household_not_found_returns_null()
    {
        $service = new ImportHouseholdLookupService;
        $result = $service->lookupByIC('999999999999');

        $this->assertNull($result);
    }

    /** @test */
    public function validation_service_detects_invalid_ic()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'validation_test',
            'status' => 'extracted',
            'page_count' => 1,
            'extracted_data' => [
                'nama' => ['value' => 'Ali', 'confidence' => 95],
                'ic' => ['value' => 'abc', 'confidence' => 60],
            ],
            'overall_confidence' => 77,
        ]);

        $service = new ImportValidationService;
        $result = $service->validate($import);

        $this->assertTrue($result['has_critical_errors']);
        $this->assertArrayHasKey('ic', $result['fields']);
        $this->assertEquals('error', $result['fields']['ic']['severity']);
    }

    /** @test */
    public function commit_creates_profil_asnaf()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'commit_test',
            'status' => 'extracted',
            'page_count' => 1,
            'extracted_data' => [
                'nama' => ['value' => 'Mohd Faizal Bin Hassan', 'confidence' => 95],
                'ic' => ['value' => '800315015678', 'confidence' => 99],
                'telefon' => ['value' => '0123456789', 'confidence' => 90],
                'alamat' => ['value' => 'Johor Bahru', 'confidence' => 88],
                'pendapatan' => ['value' => '1200', 'confidence' => 85],
                'bilangan_tanggungan' => ['value' => '3', 'confidence' => 90],
                'sewa' => ['value' => '450', 'confidence' => 80],
                'utiliti' => ['value' => '150', 'confidence' => 78],
            ],
            'overall_confidence' => 88,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('import-pdf.commit', $import->id), [
                'nama' => 'Mohd Faizal Bin Hassan',
                'ic' => '800315015678',
                'pendapatan' => 1200,
                'bilangan_tanggungan' => 3,
                'sewa' => 450,
                'utiliti' => 150,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('profil_asnafs', [
            'nama' => 'Mohd Faizal Bin Hassan',
            'ic' => '800315015678',
            'pendapatan' => 1200,
        ]);

        $this->assertDatabaseHas('document_imports', [
            'id' => $import->id,
            'status' => 'committed',
            'profil_asnaf_ic' => '800315015678',
        ]);
    }

    /** @test */
    public function unauthorised_user_cannot_access_import()
    {
        $response = $this->get(route('import-pdf.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function cannot_download_other_users_file()
    {
        $otherAdmin = Admin::factory()->create();
        $import = DocumentImport::create([
            'user_id' => $otherAdmin->id,
            'original_filename' => 'test.pdf',
            'stored_path' => 'ocr/other.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'other_hash',
            'status' => 'extracted',
            'page_count' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.file', $import->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function preview_shows_extracted_data_when_ready()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'preview_test',
            'status' => 'extracted',
            'page_count' => 2,
            'extracted_data' => [
                'nama' => ['value' => 'Ali Bin Abu', 'confidence' => 98],
                'ic' => ['value' => '900101145678', 'confidence' => 99],
            ],
            'overall_confidence' => 98.5,
            'ocr_engine' => 'pdfparser',
            'ocr_completed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.preview', $import->id));

        $response->assertStatus(200);
        $response->assertSee('Ali Bin Abu');
        $response->assertSee('98.5');
        $response->assertSee('Keyakinan AI');
    }

    /** @test */
    public function preview_shows_processing_when_still_processing()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang.pdf',
            'stored_path' => 'ocr/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'processing_test',
            'status' => 'processing',
            'page_count' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.preview', $import->id));

        $response->assertStatus(200);
        $response->assertSee('Sedang Memproses');
    }

    /** @test */
    public function history_page_shows_all_records()
    {
        DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'test1.pdf',
            'stored_path' => 'ocr/test1.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'history_1',
            'status' => 'extracted',
            'page_count' => 1,
        ]);

        DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'test2.pdf',
            'stored_path' => 'ocr/test2.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'file_hash' => 'history_2',
            'status' => 'committed',
            'page_count' => 2,
            'profil_asnaf_ic' => '800315015678',
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.history'));

        $response->assertStatus(200);
        $response->assertSee('test1.pdf');
        $response->assertSee('test2.pdf');
        $response->assertSee('800315015678');
    }

    /** @test */
    public function needs_review_status_is_set_for_low_confidence()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'lowconf.pdf',
            'stored_path' => 'ocr/lowconf.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'file_hash' => 'lowconf_test',
            'status' => 'extracted',
            'page_count' => 1,
            'extracted_data' => [
                'nama' => ['value' => 'Ali', 'confidence' => 40],
                'ic' => ['value' => '123', 'confidence' => 30],
            ],
            'overall_confidence' => 35,
        ]);

        $service = new ImportValidationService;
        $result = $service->validate($import);

        $this->assertTrue($result['has_critical_errors']);
    }

    /** @test */
    public function show_page_displays_import_details()
    {
        $import = DocumentImport::create([
            'user_id' => $this->admin->id,
            'original_filename' => 'borang_maij.pdf',
            'stored_path' => 'ocr/maij.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 5120,
            'file_hash' => 'show_test',
            'status' => 'extracted',
            'page_count' => 3,
            'extracted_data' => [
                'nama' => ['value' => 'Test User', 'confidence' => 90],
            ],
            'overall_confidence' => 90,
            'ocr_engine' => 'tesseract',
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.show', $import->id));

        $response->assertStatus(200);
        $response->assertSee('borang_maij.pdf');
        $response->assertSee('tesseract');
        $response->assertSee('3');
    }
}
