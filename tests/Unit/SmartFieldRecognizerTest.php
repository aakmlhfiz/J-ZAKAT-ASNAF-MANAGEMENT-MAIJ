<?php

namespace Tests\Unit;

use App\Services\Ocr\SmartFieldRecognizer;
use Tests\TestCase;

class SmartFieldRecognizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ocr.ai_recognizer' => 'mock']);
    }

    private function makeResult(array $fields, float $confidence): array
    {
        return [
            'fields' => $fields,
            'overall_confidence' => $confidence,
            'matched_labels' => [],
            'household_members' => [],
            'sections_detected' => [],
        ];
    }

    public function test_mock_enhance_normalises_ic(): void
    {
        $result = $this->makeResult([
            'ic' => ['value' => '800315-01-5678', 'confidence' => 80, 'source_label' => 'No. K/P', 'needs_review' => false],
        ], 80);

        $enhanced = (new SmartFieldRecognizer)->enhance($result, 'No. K/P: 800315-01-5678');

        $this->assertSame('800315015678', $enhanced['fields']['ic']['value'] ?? null);
        $this->assertGreaterThan(80, $enhanced['fields']['ic']['confidence'] ?? 0);
    }

    public function test_mock_enhance_formats_monetary_values(): void
    {
        $result = $this->makeResult([
            'pendapatan' => ['value' => 'RM 1,500', 'confidence' => 80, 'source_label' => 'Pendapatan', 'needs_review' => false],
        ], 80);

        $enhanced = (new SmartFieldRecognizer)->enhance($result, 'Pendapatan: RM 1,500');

        $this->assertSame('1500.00', $enhanced['fields']['pendapatan']['value'] ?? null);
    }

    public function test_mock_enhance_normalises_boolean_fields(): void
    {
        $result = $this->makeResult([
            'merokok' => ['value' => 'ya', 'confidence' => 90, 'source_label' => 'Merokok', 'needs_review' => false],
        ], 90);

        $enhanced = (new SmartFieldRecognizer)->enhance($result, 'Merokok: ya');

        $this->assertSame('Ya', $enhanced['fields']['merokok']['value'] ?? null);
    }

    public function test_enhance_returns_regex_result_when_recognizer_disabled(): void
    {
        config(['ocr.ai_recognizer' => '']);
        $result = $this->makeResult([
            'nama' => ['value' => 'Ali Bin Abu', 'confidence' => 95, 'source_label' => 'Nama Pemohon', 'needs_review' => false],
        ], 95);

        $enhanced = (new SmartFieldRecognizer)->enhance($result, 'Nama Pemohon: Ali Bin Abu');

        $this->assertSame($result, $enhanced);
    }
}
