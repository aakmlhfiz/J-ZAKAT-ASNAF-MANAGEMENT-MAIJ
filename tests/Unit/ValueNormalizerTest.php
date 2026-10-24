<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\ValueNormalizer;
use PHPUnit\Framework\TestCase;

class ValueNormalizerTest extends TestCase
{
    private ValueNormalizer $n;

    protected function setUp(): void
    {
        parent::setUp();
        $this->n = new ValueNormalizer;
    }

    public function test_ic_with_separators_is_normalized_to_twelve_digits(): void
    {
        $r = $this->n->normalize('ic', '800315-01-5678', 'ic');

        $this->assertSame('800315015678', $r['value']);
        $this->assertSame('800315-01-5678', $r['original_value']);
        $this->assertSame('800315015678', $r['normalized_value']);
    }

    public function test_ic_short_value_is_rejected(): void
    {
        $r = $this->n->normalize('ic', '123', 'ic');

        $this->assertNull($r['value']);
        $this->assertNull($r['original_value']);
    }

    public function test_phone_restores_dropped_leading_zero(): void
    {
        $r = $this->n->normalize('no_telefon_bimbit', '192334003', 'phone');

        $this->assertSame('0192334003', $r['value']);
    }

    public function test_phone_without_leading_zero_ten_digits_is_not_forced(): void
    {
        $r = $this->n->normalize('no_telefon_bimbit', '9192334003', 'phone');

        $this->assertSame('9192334003', $r['value']);
        $this->assertSame('9192334003', $r['original_value']);
    }

    public function test_money_is_normalized_to_two_decimals(): void
    {
        $r = $this->n->normalize('jumlah', 'RM 1,500.00', 'amount');

        $this->assertSame('1500.00', $r['value']);
        $this->assertSame('RM 1,500.00', $r['original_value']);
    }

    public function test_amount_garbage_is_rejected_not_passed_through(): void
    {
        $r = $this->n->normalize('jumlah', 'RM', 'amount');

        $this->assertNull($r['value']);
        $this->assertNull($r['original_value']);
    }

    public function test_date_dd_mm_yyyy_is_normalized(): void
    {
        $r = $this->n->normalize('pengakuan_tarikh', '12/08/2026', 'date');

        $this->assertSame('2026-08-12', $r['value']);
    }

    public function test_date_month_name_is_preserved_as_month_year(): void
    {
        $r = $this->n->normalize('perubatan_mula_mengidap', 'Feb 2026', 'date');

        $this->assertSame('2026-02', $r['value']);
    }

    public function test_sda_token_is_preserved_verbatim(): void
    {
        $r = $this->n->normalize('pekerjaan_pemohon_jawatan', 'S.D.A', 'text');

        $this->assertSame('S.D.A', $r['value']);
    }

    public function test_postcode_extracts_five_digits(): void
    {
        $r = $this->n->normalize('poskod', 'Poskod : 36000', 'poskod');

        $this->assertSame('36000', $r['value']);
    }

    public function test_empty_value_returns_all_nulls(): void
    {
        $r = $this->n->normalize('nama', '', 'text');

        $this->assertNull($r['value']);
        $this->assertNull($r['original_value']);
    }
}
