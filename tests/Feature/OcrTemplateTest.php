<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\Ocr\DocumentIntelligence\MaiFormTemplate;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class OcrTemplateTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $templatePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'email' => 'tpl-'.uniqid().'@maij.gov.my',
            'password' => bcrypt('password'),
        ]);
        $this->templatePath = (string) config('ocr.document_intelligence.template_pdf');
    }

    public function test_template_pdf_exists_and_has_four_pages(): void
    {
        $this->assertFileExists($this->templatePath);

        $parser = new Parser;
        $pdf = $parser->parseFile($this->templatePath);

        $this->assertCount(4, $pdf->getPages());
    }

    public function test_template_download_requires_authentication(): void
    {
        $response = $this->get(route('import-pdf.template'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_download_template_and_is_logged(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('import-pdf.template'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'activity' => 'ocr_template_downloaded',
        ]);
    }

    public function test_calibration_command_generates_baselines_json(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->templatePath)) {
            $this->markTestSkipped('Template PDF missing');
        }

        $calibrationPath = sys_get_temp_dir().'/maij_calibration_'.uniqid().'.json';
        config(['ocr.document_intelligence.calibration_path' => $calibrationPath]);

        try {
            $exit = Artisan::call('ocr:calibrate-template');
            $this->assertSame(0, $exit);

            $this->assertFileExists($calibrationPath);
            $data = json_decode((string) File::get($calibrationPath), true);

            $this->assertSame(4, $data['page_count']);
            $this->assertSame('MAIJ-Borang-Agihan-Zakat-blank.pdf', $data['source_pdf']);

            $baselines = $data['checkbox_baselines'];
            $this->assertArrayHasKey('kesihatan.sihat', $baselines);
            $this->assertArrayHasKey('status_perkahwinan.bujang', $baselines);
            $this->assertArrayHasKey('merokok.tidak', $baselines);

            $this->assertEqualsWithDelta(0.0044, $baselines['kesihatan.sihat'], 0.003);
            $this->assertEqualsWithDelta(0.0022, $baselines['status_perkahwinan.bujang'], 0.003);
            $this->assertArrayHasKey('signature_pemohon', $data['ink_zones']);
        } finally {
            @unlink($calibrationPath);
        }
    }

    public function test_calibration_command_dry_run_does_not_write_file(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->templatePath)) {
            $this->markTestSkipped('Template PDF missing');
        }

        $calibrationPath = sys_get_temp_dir().'/maij_calibration_dry_'.uniqid().'.json';
        config(['ocr.document_intelligence.calibration_path' => $calibrationPath]);

        try {
            $exit = Artisan::call('ocr:calibrate-template', ['--dry-run' => true]);
            $this->assertSame(0, $exit);
            $this->assertFileDoesNotExist($calibrationPath);
        } finally {
            @unlink($calibrationPath);
        }
    }

    public function test_template_baselines_are_loaded_from_calibration_json(): void
    {
        $baselines = MaiFormTemplate::checkboxBaselines();

        $this->assertArrayHasKey('kesihatan.sihat', $baselines);
        $this->assertArrayHasKey('status_perkahwinan.duda', $baselines);

        $inkZones = MaiFormTemplate::inkZones();
        $this->assertArrayHasKey('signature_pemohon', $inkZones);
        $this->assertArrayHasKey('baseline', $inkZones['signature_pemohon']);
    }
}
