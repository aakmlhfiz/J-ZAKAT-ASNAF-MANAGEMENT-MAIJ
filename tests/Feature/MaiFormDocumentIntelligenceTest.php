<?php

namespace Tests\Feature;

use App\Services\Ocr\DocumentIntelligence\BorangFormUnderstanding;
use App\Services\Ocr\Extractors\TesseractExtractor;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class MaiFormDocumentIntelligenceTest extends TestCase
{
    private string $filled;

    private string $blank;

    protected function setUp(): void
    {
        parent::setUp();

        $dir = storage_path('app/private/ocr');
        $this->filled = $dir.'/9027b0ac-4da9-4ef7-bf30-47a3998cef2c.pdf';
        $this->blank = $dir.'/3e12c7d0-0a74-4288-828a-c89725c0672e.pdf';
    }

    private function runEngine(string $file): array
    {
        $ocrText = '';
        if (file_exists($file)) {
            $parser = new Parser;
            $pdf = $parser->parseFile($file);
            $parts = [];
            foreach ($pdf->getPages() as $i => $pg) {
                $t = trim($pg->getText());
                if ($t !== '') {
                    $parts[] = '--- PAGE '.($i + 1)." ---\n".$t;
                }
            }
            $ocrText = implode("\n\n", $parts);
        }

        return (new BorangFormUnderstanding)->understand($file, 'application/pdf', $ocrText, ['page_count' => 4]);
    }

    public function test_real_filled_form_extracts_applicant_and_checkboxes(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->filled)) {
            $this->markTestSkipped('Real filled form fixture missing');
        }

        $result = $this->runEngine($this->filled);
        $fields = $result['fields'];
        $cb = $result['checkboxes'];

        $this->assertSame('Muhammad Akmal Hafiz bin Shamsul Bahare', $fields['nama']['value']);
        $this->assertSame('Hilir Perak', $fields['daerah']['value']);
        $this->assertSame('36000', $fields['poskod']['value']);

        $this->assertTrue($cb['kesihatan']['sihat']['value']);
        $this->assertFalse($cb['kesihatan']['sakit']['value']);
        $this->assertTrue($cb['status_perkahwinan']['bujang']['value']);
        $this->assertTrue($cb['merokok']['tidak']['value']);

        $apps = collect($result['applications'])->keyBy('type');
        $this->assertSame('500.00', $apps['Bantuan Sara Hidup']['amount_requested']);
        $this->assertSame('500.00', $apps['Bantuan Perubatan']['amount_requested']);
        $this->assertSame('800.00', $apps['Menjelaskan Hutang']['amount_requested']);

        // Every field carries page + input_type metadata.
        $this->assertSame(1, $fields['nama']['page']);
        $this->assertContains($fields['nama']['input_type'], ['printed', 'typed', 'handwritten']);
    }

    public function test_real_blank_form_does_not_invent_values(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->blank)) {
            $this->markTestSkipped('Real blank form fixture missing');
        }

        $result = $this->runEngine($this->blank);

        $this->assertNull($result['fields']['nama']['value']);
        $this->assertNull($result['fields']['daerah']['value']);
        $this->assertNull($result['fields']['poskod']['value']);

        foreach ($result['applications'] as $app) {
            $this->assertNull($app['amount_requested']);
        }

        $this->assertFalse($result['declaration']['signature']['present']);
    }

    public function test_engine_reports_pages_and_document_type(): void
    {
        if (! TesseractExtractor::isAvailable() || ! class_exists(\Imagick::class)) {
            $this->markTestSkipped('Tesseract/Imagick not available');
        }
        if (! file_exists($this->filled)) {
            $this->markTestSkipped('Real filled form fixture missing');
        }

        $result = $this->runEngine($this->filled);

        $this->assertSame(4, $result['document']['pages']);
        $this->assertSame('MAIJ Zakat Assistance Application Form', $result['document']['document_type']);
        $this->assertNotEmpty($result['document']['sections_detected']);
    }
}
