<?php

namespace Tests\Feature;

use App\Services\Ocr\DocumentIntelligence\Vision\VisionFormUnderstanding;
use Tests\TestCase;

class VisionFormUnderstandingTest extends TestCase
{
    private string $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->template = (string) config('ocr.document_intelligence.template_pdf');
    }

    private function fixture(): array
    {
        return [
            'document' => [
                'pages' => 4,
                'sections_detected' => ['4. MAKLUMAT PENGENALAN PEMOHON', '11. PENGAKUAN PEMOHON'],
            ],
            'applicant' => [
                'nama' => ['value' => 'Muhammad Akmal Hafiz bin Shamsul Bahare', 'confidence' => 92, 'page' => 1, 'input_type' => 'printed', 'bbox' => ['x' => 300, 'y' => 1700, 'width' => 800, 'height' => 40]],
                'ic' => ['value' => '800315-01-5678', 'confidence' => 99, 'page' => 1, 'input_type' => 'printed', 'bbox' => null],
                'daerah' => ['value' => 'Hilir Perak', 'confidence' => 90, 'page' => 1, 'input_type' => 'printed', 'bbox' => null],
                'poskod' => ['value' => '36000', 'confidence' => 98, 'page' => 1, 'input_type' => 'printed', 'bbox' => null],
                'telefon_bimbit_typo' => ['value' => 'x', 'confidence' => 10, 'page' => 1, 'input_type' => 'handwritten', 'bbox' => null],
            ],
            'applications' => [
                ['type' => 'Bantuan Sara Hidup', 'selected' => true, 'amount_requested' => 'RM 500.00', 'reason' => 'Gaji tidak mencukupi', 'page' => 1],
                ['type' => 'Menjelaskan Hutang', 'selected' => true, 'amount_requested' => '800', 'reason' => null, 'page' => 1],
            ],
            'sara_hidup' => [
                'jumlah_sara_hidup' => ['value' => '1500', 'confidence' => 90, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
                'no_akaun_bimb' => ['value' => '5555 5555', 'confidence' => 80, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
            ],
            'medical' => [
                'perubatan_nama_tanggungan' => ['value' => 'MUHAMMAD DANIAL BIN SHAHARUDDIN', 'confidence' => 85, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
                'perubatan_no_kp_tanggungan' => ['value' => '040311084031', 'confidence' => 90, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
                'perubatan_kos_rawatan' => ['value' => '15000', 'confidence' => 88, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
                'perubatan_mula_mengidap' => ['value' => 'Feb 2026', 'confidence' => 85, 'page' => 2, 'input_type' => 'typed', 'bbox' => null],
            ],
            'dependents' => [
                ['row' => 1, 'nama_penuh' => 'MUHAMMAD RUSYDI BIN MD RAHFEE', 'no_ic' => '040311084031', 'sekolah_pekerjaan' => 'Kelab', 'pendapatan' => '1800', 'hubungan' => 'Kawan', 'tinggal_bersama' => 'Sendiri'],
            ],
            'income' => ['pendapatan_diri' => ['value' => '1500', 'confidence' => 80, 'page' => 3]],
            'expenses' => ['perbelanjaan_makan_minum' => ['value' => 'RM 600.00', 'confidence' => 80, 'page' => 3]],
            'declaration' => [
                'pengakuan_nama' => ['value' => 'Muhammad Akmal Hafiz bin Shamsul Bahare', 'confidence' => 90, 'page' => 4, 'input_type' => 'printed', 'bbox' => null],
                'pengakuan_tarikh' => ['value' => '12/08/2026', 'confidence' => 85, 'page' => 4, 'input_type' => 'printed', 'bbox' => null],
                'signature' => ['present' => true, 'confidence' => 80],
            ],
            'verification' => [
                'pengesah_nama' => ['value' => 'MUHAMMAD DANIAL BIN SHAHARUDDIN', 'confidence' => 90, 'page' => 4, 'input_type' => 'printed', 'bbox' => null],
                'pengesah_jawatan' => ['value' => 'Ketua Kampung', 'confidence' => 90, 'page' => 4, 'input_type' => 'printed', 'bbox' => null],
                'stamp' => ['present' => false, 'confidence' => 70],
            ],
            'checkboxes' => [
                'kesihatan' => ['sihat' => true, 'sakit' => false, 'cacat' => null],
                'merokok' => ['ya' => false, 'tidak' => true],
            ],
        ];
    }

    public function test_vision_is_supported_when_enabled(): void
    {
        config(['ocr.vision.enabled' => true, 'ocr.vision.provider' => 'mock']);
        $vision = new VisionFormUnderstanding(null, $this->fixture());

        $this->assertTrue($vision->supported());
    }

    public function test_vision_not_supported_when_disabled(): void
    {
        config(['ocr.vision.enabled' => false]);
        $vision = new VisionFormUnderstanding;

        $this->assertFalse($vision->supported());
    }

    public function test_vision_normalises_model_output_into_ocr_result(): void
    {
        if (! class_exists(\Imagick::class) || ! file_exists($this->template)) {
            $this->markTestSkipped('Imagick or template PDF unavailable');
        }

        config(['ocr.vision.enabled' => true, 'ocr.vision.provider' => 'mock', 'ocr.vision.confidence_threshold' => 60]);

        $vision = new VisionFormUnderstanding(null, $this->fixture());
        $result = $vision->understand($this->template, 'application/pdf', '', ['page_count' => 4]);

        $this->assertNotNull($result);
        $this->assertSame('vision', $result['source']);
        $this->assertSame('mock', $result['provider']);
        $this->assertSame(4, $result['document']['pages']);

        // Applicant + normalization (IC separators removed).
        $this->assertSame('Muhammad Akmal Hafiz bin Shamsul Bahare', $result['applicant']['nama']['value']);
        $this->assertSame('800315015678', $result['applicant']['ic']['value']);
        $this->assertSame('800315-01-5678', $result['applicant']['ic']['original_value']);
        $this->assertSame(99, $result['applicant']['ic']['confidence']);

        // Applications + amount normalization.
        $apps = collect($result['applications'])->keyBy('type');
        $this->assertTrue($apps['Bantuan Sara Hidup']['selected']);
        $this->assertSame('500.00', $apps['Bantuan Sara Hidup']['amount_requested']);
        $this->assertSame('800.00', $apps['Menjelaskan Hutang']['amount_requested']);

        // Medical + date/amount normalization.
        $this->assertSame('MUHAMMAD DANIAL BIN SHAHARUDDIN', $result['medical']['perubatan_nama_tanggungan']['value']);
        $this->assertSame('15000.00', $result['medical']['perubatan_kos_rawatan']['value']);
        $this->assertSame('2026-02', $result['medical']['perubatan_mula_mengidap']['value']);

        // Dependents.
        $this->assertSame('MUHAMMAD RUSYDI BIN MD RAHFEE', $result['dependents'][0]['nama_penuh']);
        $this->assertSame(1800, $result['dependents'][0]['pendapatan']);
        $this->assertCount(6, $result['dependents']);

        // Income / expenses.
        $this->assertSame('1500.00', $result['income']['pendapatan_diri']['value']);
        $this->assertSame('600.00', $result['expenses']['perbelanjaan_makan_minum']['value']);

        // Financial audit.
        $this->assertArrayHasKey('financial_audit', $result);
        $this->assertSame(1500.0, $result['financial_audit']['income_sum']);
        $this->assertSame(600.0, $result['financial_audit']['expense_sum']);
        $this->assertContains($result['financial_audit']['overall'], ['pass', 'warn', 'fail']);

        // Declaration / verification + signature.
        $this->assertSame('2026-08-12', $result['declaration']['pengakuan_tarikh']['value']);
        $this->assertTrue($result['declaration']['signature']['present']);
        $this->assertFalse($result['verification']['stamp']['present']);

        // Checkboxes (tri-state).
        $this->assertTrue($result['checkboxes']['kesihatan']['sihat']['value']);
        $this->assertFalse($result['checkboxes']['kesihatan']['sakit']['value']);
        $this->assertNull($result['checkboxes']['kesihatan']['cacat']['value']);
        $this->assertTrue($result['checkboxes']['merokok']['tidak']['value']);

        // Sections detected.
        $this->assertNotEmpty($result['document']['sections_detected']);
        $this->assertSame('MAKLUMAT PENGENALAN PEMOHON', $result['document']['sections_detected'][0]['title']);
    }

    public function test_low_confidence_and_unreadable_fields_flagged(): void
    {
        if (! class_exists(\Imagick::class) || ! file_exists($this->template)) {
            $this->markTestSkipped('Imagick or template PDF unavailable');
        }

        config(['ocr.vision.enabled' => true, 'ocr.vision.provider' => 'mock', 'ocr.vision.confidence_threshold' => 60]);

        $fixture = $this->fixture();
        $fixture['applicant']['nama']['confidence'] = 30;
        $fixture['applicant']['no_telefon_bimbit'] = ['value' => null, 'confidence' => 10, 'page' => 1, 'input_type' => null, 'bbox' => null];

        $vision = new VisionFormUnderstanding(null, $fixture);
        $result = $vision->understand($this->template, 'application/pdf', '', ['page_count' => 4]);

        $this->assertTrue($result['applicant']['nama']['needs_review']);
        $this->assertNull($result['applicant']['no_telefon_bimbit']['value']);
        $this->assertTrue($result['applicant']['no_telefon_bimbit']['needs_review']);
    }
}
