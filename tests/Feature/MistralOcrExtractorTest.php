<?php

namespace Tests\Feature;

use App\Services\Ocr\Extractors\MistralOcrExtractor;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MistralOcrExtractorTest extends TestCase
{
    private string $imagePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imagePath = tempnam(sys_get_temp_dir(), 'mistral_').'.pdf';
        file_put_contents($this->imagePath, $this->makePdf(1));

        config(['ocr.mistral.api_key' => 'test-key']);
        config(['ocr.mistral.base_url' => 'https://api.mistral.ai']);
        config(['ocr.mistral.model' => 'mistral-ocr-latest']);
        config(['ocr.mistral.timeout' => 5]);
        config(['ocr.mistral.max_pages' => 10]);
        config(['ocr.mistral.retry_attempts' => 2]);
        config(['ocr.mistral.retry_backoff_ms' => 1]);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->imagePath)) {
            @unlink($this->imagePath);
        }
        parent::tearDown();
    }

    public function test_extract_flattens_pages_from_successful_response(): void
    {
        Http::fake([
            'api.mistral.ai/*' => Http::response([
                'pages' => [
                    ['markdown' => 'Nama: Ali Bin Abu'],
                    ['markdown' => 'Pendapatan: RM 1500'],
                ],
            ], 200),
        ]);

        $text = (new MistralOcrExtractor)->extract($this->imagePath);

        $this->assertStringContainsString('Nama: Ali Bin Abu', $text);
        $this->assertStringContainsString('Pendapatan: RM 1500', $text);
    }

    public function test_extract_retries_on_transient_http_error(): void
    {
        $calls = 0;

        Http::fake(function ($request) use (&$calls) {
            $calls++;

            if ($calls === 1) {
                return Http::response(['detail' => 'rate limited'], 429);
            }

            return Http::response(['pages' => [['markdown' => 'Nama: Ali Bin Abu']]], 200);
        });

        $text = (new MistralOcrExtractor)->extract($this->imagePath);

        $this->assertStringContainsString('Ali Bin Abu', $text);
        $this->assertGreaterThanOrEqual(2, $calls);
    }

    public function test_extract_surfaces_needs_processing_status(): void
    {
        Http::fake([
            'api.mistral.ai/*' => Http::response(['status' => 'needs_processing', 'pages' => []], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('needs_processing');

        (new MistralOcrExtractor)->extract($this->imagePath);
    }

    public function test_extract_throws_when_page_count_exceeds_limit(): void
    {
        $pdfPath = tempnam(sys_get_temp_dir(), 'mistral_pdf_').'.pdf';
        file_put_contents($pdfPath, $this->makePdf(2));

        config(['ocr.mistral.max_pages' => 1]);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('melebihi had');

            (new MistralOcrExtractor)->extract($pdfPath);
        } finally {
            @unlink($pdfPath);
        }
    }

    private function makePdf(int $pageCount): string
    {
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj",
        ];

        $kids = implode(' ', array_map(fn ($i) => ($i + 2).' 0 R', range(1, $pageCount)));
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [{$kids}] /Count {$pageCount} >>\nendobj";

        $fontObj = 2 + ($pageCount * 2) + 1;

        foreach (range(1, $pageCount) as $i) {
            $pageObj = $i + 2;
            $contentObj = $i + 2 + $pageCount;
            $objects[] = "{$pageObj} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents {$contentObj} 0 R /Resources << /Font << /F1 {$fontObj} 0 R >> >> >>\nendobj";
        }

        foreach (range(1, $pageCount) as $i) {
            $content = "BT /F1 12 Tf 72 720 Td (Page {$i}) Tj ET";
            $contentObj = $i + 2 + $pageCount;
            $objects[] = "{$contentObj} 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream\nendobj";
        }

        $objects[] = "{$fontObj} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";

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
}
