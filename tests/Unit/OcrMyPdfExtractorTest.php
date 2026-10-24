<?php

namespace Tests\Unit;

use App\Services\Ocr\Extractors\OcrMyPdfExtractor;
use ReflectionProperty;
use Tests\TestCase;

class OcrMyPdfExtractorTest extends TestCase
{
    public function test_tessdata_prefix_comes_from_config_not_hardcoded(): void
    {
        config(['ocr.ocr_my_pdf.tessdata_prefix' => '/opt/tessdata']);

        $extractor = new OcrMyPdfExtractor;
        $reflection = new ReflectionProperty(OcrMyPdfExtractor::class, 'tessdataPrefix');

        $this->assertSame('/opt/tessdata', $reflection->getValue($extractor));
    }

    public function test_tessdata_prefix_defaults_to_empty_string(): void
    {
        config(['ocr.ocr_my_pdf.tessdata_prefix' => '']);

        $extractor = new OcrMyPdfExtractor;
        $reflection = new ReflectionProperty(OcrMyPdfExtractor::class, 'tessdataPrefix');

        $this->assertSame('', $reflection->getValue($extractor));
    }

    public function test_binary_language_and_timeout_from_config(): void
    {
        config(['ocr.ocr_my_pdf.binary' => '/usr/bin/python3']);
        config(['ocr.ocr_my_pdf.language' => 'msa+eng']);
        config(['ocr.ocr_my_pdf.timeout' => 30]);

        $extractor = new OcrMyPdfExtractor;

        $binary = new ReflectionProperty(OcrMyPdfExtractor::class, 'binary');
        $language = new ReflectionProperty(OcrMyPdfExtractor::class, 'language');
        $timeout = new ReflectionProperty(OcrMyPdfExtractor::class, 'timeout');

        $this->assertSame('/usr/bin/python3', $binary->getValue($extractor));
        $this->assertSame('msa+eng', $language->getValue($extractor));
        $this->assertSame(30, $timeout->getValue($extractor));
    }
}
