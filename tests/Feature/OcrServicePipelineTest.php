<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentImport;
use App\Models\Admin;
use App\Models\DocumentImport;
use App\Services\Ocr\BorangBantuanRecognizer;
use App\Services\Ocr\Extractors\PdfTextExtractor;
use App\Services\Ocr\OcrService;
use App\Services\Ocr\RegexFieldRecognizer;
use App\Services\Ocr\SmartFieldRecognizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class OcrServicePipelineTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'email' => 'ocr-'.uniqid().'@maij.gov.my',
        ]);
    }

    private function makePdf(string $text): string
    {
        $content = "BT /F1 12 Tf 72 720 Td ({$text}) Tj ET";
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj",
            "4 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream\nendobj",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj."\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefPos."\n%%EOF";

        return $pdf;
    }

    private function makeImport(string $storedPath = 'ocr/test.pdf', array $overrides = []): DocumentImport
    {
        return DocumentImport::create(array_merge([
            'user_id' => $this->admin->id,
            'original_filename' => 'test.pdf',
            'stored_path' => $storedPath,
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'file_hash' => uniqid('hash_', true),
            'status' => 'processing',
            'page_count' => 1,
        ], $overrides));
    }

    private function regexResult(float $confidence, array $fields = []): array
    {
        return [
            'fields' => $fields,
            'overall_confidence' => $confidence,
            'matched_labels' => [],
            'household_members' => [],
            'sections_detected' => [],
        ];
    }

    public function test_process_text_layer_marks_extracted_when_clean_and_complete(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/test.pdf', $this->makePdf('Nama: Ali Bin Abu'));
        config(['ocr.default' => 'pdfparser']);
        config(['ocr.confidence_threshold' => 70]);

        $import = $this->makeImport();

        $fields = [
            'nama' => ['value' => 'Ali Bin Abu', 'confidence' => 95],
            'ic' => ['value' => '800315015678', 'confidence' => 99],
        ];

        $regex = Mockery::mock(RegexFieldRecognizer::class);
        $regex->shouldReceive('recognize')->once()->andReturn($this->regexResult(97, $fields));

        $ocr = new OcrService($regex, new SmartFieldRecognizer, new BorangBantuanRecognizer);

        $this->assertTrue($ocr->processTextLayer($import));

        $import->refresh();
        $this->assertSame('extracted', $import->status);
        $this->assertSame('Ali Bin Abu', $import->extracted_data['nama']['value']);
    }

    public function test_process_text_layer_marks_needs_review_when_critical_field_missing(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/test.pdf', $this->makePdf('Nama sahaja'));
        config(['ocr.default' => 'pdfparser']);
        config(['ocr.confidence_threshold' => 70]);

        $import = $this->makeImport();

        $fields = [
            'nama' => ['value' => 'Ali Bin Abu', 'confidence' => 95],
            // ic missing → needs_review
        ];

        $regex = Mockery::mock(RegexFieldRecognizer::class);
        $regex->shouldReceive('recognize')->once()->andReturn($this->regexResult(95, $fields));

        $ocr = new OcrService($regex, new SmartFieldRecognizer, new BorangBantuanRecognizer);

        $this->assertTrue($ocr->processTextLayer($import));

        $import->refresh();
        $this->assertSame('needs_review', $import->status);
    }

    public function test_process_text_layer_marks_needs_review_when_below_threshold(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/test.pdf', $this->makePdf('teks'));
        config(['ocr.default' => 'pdfparser']);
        config(['ocr.confidence_threshold' => 70]);

        $import = $this->makeImport();

        $fields = [
            'nama' => ['value' => 'Ali', 'confidence' => 60],
            'ic' => ['value' => '800315015678', 'confidence' => 55],
        ];

        $regex = Mockery::mock(RegexFieldRecognizer::class);
        $regex->shouldReceive('recognize')->once()->andReturn($this->regexResult(58, $fields));

        $ocr = new OcrService($regex, new SmartFieldRecognizer, new BorangBantuanRecognizer);

        $this->assertTrue($ocr->processTextLayer($import));

        $import->refresh();
        $this->assertSame('needs_review', $import->status);
    }

    public function test_borang_single_pass_runs_enhance_once_and_reuses_results(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/test.pdf', $this->makePdf('Nama: Ali Bin Abu'));
        config(['ocr.default' => 'pdfparser']);
        config(['ocr.confidence_threshold' => 70]);

        $import = $this->makeImport('ocr/test.pdf', [
            'document_type' => OcrService::DOC_TYPE_BORANG_BANTUAN,
            'template' => 'Borang Agihan Zakat',
        ]);

        $fields = [
            'nama' => ['value' => 'Ali Bin Abu', 'confidence' => 95],
            'ic' => ['value' => '800315015678', 'confidence' => 99],
        ];

        $regex = Mockery::mock(RegexFieldRecognizer::class);
        $regex->shouldReceive('recognize')->once()->andReturn($this->regexResult(80, $fields));

        $smart = Mockery::mock(SmartFieldRecognizer::class);
        $smart->shouldReceive('enhance')->once()->andReturn($this->regexResult(85, $fields));

        $borangResult = [
            'fields' => $fields,
            'household_members' => [],
            'overall_confidence' => 88,
            'matched_labels' => [],
            'sections_detected' => [],
            'borang' => ['checkboxes' => [], 'pendapatan' => [], 'perbelanjaan' => [], 'harta' => [], 'pekerjaan' => []],
        ];

        $borang = Mockery::mock(BorangBantuanRecognizer::class);
        $borang->shouldReceive('recognizeFrom')->once()->withArgs(function ($text, $regexArg, $enhancedArg) {
            return ($regexArg['fields']['nama']['value'] ?? null) === 'Ali Bin Abu'
                && (float) ($enhancedArg['overall_confidence'] ?? 0) == 85;
        })->andReturn($borangResult);

        $ocr = new OcrService($regex, $smart, $borang);

        $this->assertTrue($ocr->processTextLayer($import));

        $import->refresh();
        $this->assertSame(88, (int) $import->overall_confidence);
        $this->assertArrayHasKey('checkboxes', $import->borang_data);
    }

    public function test_process_reraises_and_job_marks_failed_with_sanitized_message_on_final_attempt(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ocr/corrupt.pdf', "this is not a real pdf \x00\x01 garbage");
        config(['ocr.default' => 'pdfparser']);

        $import = $this->makeImport('ocr/corrupt.pdf');

        $ocr = new OcrService;

        $job = Mockery::mock(ProcessDocumentImport::class, [$import->id])->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        try {
            $job->handle($ocr);
        } catch (\Throwable $e) {
            // Expected: the job re-throws so the queue worker can track failure.
        }

        $import->refresh();
        $this->assertSame('failed', $import->status);
        $this->assertStringNotContainsString('\\', $import->error_message);
        $this->assertStringNotContainsString('C:', $import->error_message);
        $this->assertNotEmpty($import->error_message);
    }

    public function test_friendly_error_message_does_not_leak_internal_paths(): void
    {
        $ocr = new OcrService;

        $message = $ocr->friendlyErrorMessage(new RuntimeException('Tesseract failed at C:/Users/test/path/to/file.png'));

        $this->assertStringNotContainsString('C:/Users', $message);
        $this->assertStringNotContainsString('tesseract', mb_strtolower($message));
        $this->assertNotEmpty($message);
    }

    public function test_pdf_text_extractor_emits_page_markers(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ocr_pdf_').'.pdf';
        file_put_contents($path, $this->makePdf('Nama: Ali Bin Abu'));

        try {
            config(['ocr.page_markers.enabled' => true]);
            $text = (new PdfTextExtractor)->extract($path);
            $this->assertStringContainsString('--- PAGE 1 ---', $text);
            $this->assertStringContainsString('Ali Bin Abu', $text);

            config(['ocr.page_markers.enabled' => false]);
            $text = (new PdfTextExtractor)->extract($path);
            $this->assertStringNotContainsString('--- PAGE', $text);
        } finally {
            @unlink($path);
        }
    }
}
