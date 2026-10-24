<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\MaiFormTemplate;
use App\Services\Ocr\DocumentIntelligence\TextLayerSequenceParser;
use PHPUnit\Framework\TestCase;

class TextLayerSequenceParserTest extends TestCase
{
    private TextLayerSequenceParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new TextLayerSequenceParser;
    }

    public function test_parses_real_page2_digital_form_text_layer(): void
    {
        // Raw OCR text layer of page 2 from the filled digital MAIJ form.
        $text = '1500 5555 5555 8888 8888 MUHAMMAD DANIAL BIN SHAHARUDDIN Saudara 040311084031 Sakit Jiwa Feb 2026 Hospital Skudai 15000 20000 Muhammad Akmal Hafiz bin Shamsul Bahare 0123232';

        $result = $this->parser->parse($text, 2);

        $this->assertSame('1500', $result['jumlah_sara_hidup']['value']);
        $this->assertSame('5555 5555', $result['no_akaun_bimb']['value']);
        $this->assertSame('8888', $result['bantuan_agensi_lain_nyatakan']['value']);
        $this->assertSame('8888', $result['bantuan_agensi_lain_jumlah']['value']);

        $this->assertSame('MUHAMMAD DANIAL BIN SHAHARUDDIN', $result['perubatan_nama_tanggungan']['value']);
        $this->assertSame('Saudara', $result['perubatan_hubungan_tanggungan']['value']);
        $this->assertSame('040311084031', $result['perubatan_no_kp_tanggungan']['value']);
        $this->assertSame('Sakit Jiwa', $result['perubatan_jenis_penyakit']['value']);
        $this->assertSame('Feb 2026', $result['perubatan_mula_mengidap']['value']);
        $this->assertSame('Hospital Skudai', $result['perubatan_tempat_rawatan']['value']);
        $this->assertSame('15000', $result['perubatan_kos_rawatan']['value']);
        $this->assertSame('20000', $result['perubatan_harga_alatan']['value']);

        $this->assertSame('Muhammad Akmal Hafiz bin Shamsul Bahare', $result['nama_dalam_geran']['value']);
        $this->assertSame('0123232', $result['no_geran']['value']);

        // Every matched field has a positive confidence and no unrecognized tail.
        foreach ($result as $entry) {
            $this->assertGreaterThan(0, $entry['confidence']);
        }
        $this->assertArrayNotHasKey('_unrecognized', $result);
    }

    public function test_missing_fields_return_null_and_needs_review(): void
    {
        $text = '1500 MUHAMMAD DANIAL BIN SHAHARUDDIN 040311084031 Sakit Jiwa Feb 2026 Hospital Skudai 15000 20000 Muhammad Akmal Hafiz bin Shamsul Bahare 0123232';

        $result = $this->parser->parse($text, 2);

        // No relationship token present → hubungan null + needs_review.
        $this->assertNull($result['perubatan_hubungan_tanggungan']['value']);
        $this->assertTrue($result['perubatan_hubungan_tanggungan']['needs_review']);

        $this->assertSame('MUHAMMAD DANIAL BIN SHAHARUDDIN', $result['perubatan_nama_tanggungan']['value']);
    }

    public function test_blank_page_returns_null_fields(): void
    {
        $result = $this->parser->parse('', 2);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('jumlah_sara_hidup', $result);
        $this->assertNull($result['jumlah_sara_hidup']['value']);
        $this->assertTrue($result['jumlah_sara_hidup']['needs_review']);
    }

    public function test_page_without_spec_returns_empty(): void
    {
        $this->assertSame([], $this->parser->parse('some text', 1));
    }

    public function test_page2_spec_is_declared(): void
    {
        $spec = MaiFormTemplate::textLayerSpecs();

        $this->assertArrayHasKey(2, $spec);
        $this->assertCount(14, $spec[2]);

        $keys = array_column($spec[2], 'key');
        $this->assertContains('jumlah_sara_hidup', $keys);
        $this->assertContains('perubatan_no_kp_tanggungan', $keys);
        $this->assertContains('no_geran', $keys);
    }
}
