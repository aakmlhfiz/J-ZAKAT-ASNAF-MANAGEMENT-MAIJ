<?php

namespace Tests\Feature;

use App\Services\Ocr\Extractors\TesseractExtractor;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OcrTemplateDetectTest extends TestCase
{
    private string $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->template = (string) config('ocr.document_intelligence.template_pdf');
    }

    public function test_template_detect_reports_sections_fields_and_checkboxes(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->template)) {
            $this->markTestSkipped('Template PDF missing');
        }

        $exit = Artisan::call('ocr:template-detect');
        $output = Artisan::output();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Audit Template Borang MAIJ', $output);
        $this->assertStringContainsString('SEKSYEN DIKESAN', $output);
        $this->assertStringContainsString('MEDAN DIKESAN', $output);
        $this->assertStringContainsString('KOTAK SEMAK DIKESAN', $output);
        $this->assertStringContainsString('LABEL TIDAK DIPETAKAN', $output);
        $this->assertStringContainsString('MAKLUMAT PENGENALAN PEMOHON', $output);
        $this->assertStringContainsString('Halaman  : 4', $output);
    }

    public function test_template_detect_json_is_valid_and_structured(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->template)) {
            $this->markTestSkipped('Template PDF missing');
        }

        $exit = Artisan::call('ocr:template-detect', ['--json' => true]);
        $this->assertSame(0, $exit);

        $report = json_decode(Artisan::output(), true);
        $this->assertIsArray($report);
        $this->assertSame(4, $report['pages']);
        $this->assertSame('MAIJ-Borang-Agihan-Zakat-blank.pdf', $report['template']);

        $this->assertArrayHasKey('sections', $report);
        $this->assertArrayHasKey('fields', $report);
        $this->assertArrayHasKey('checkboxes', $report);
        $this->assertArrayHasKey('unmapped_labels', $report);

        $this->assertNotEmpty($report['fields']);
        $this->assertArrayHasKey('status', $report['fields'][0]);
        $this->assertContains($report['fields'][0]['status'], ['FOUND', 'MISSING']);
    }

    public function test_template_detect_fails_when_template_missing(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }

        config(['ocr.document_intelligence.template_pdf' => sys_get_temp_dir().'/missing-template.pdf']);

        $exit = Artisan::call('ocr:template-detect');

        $this->assertNotSame(0, $exit);
        $this->assertStringContainsString('tidak dijumpai', Artisan::output());
    }
}
