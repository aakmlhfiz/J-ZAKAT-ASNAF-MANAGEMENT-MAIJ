<?php

namespace Tests\Unit;

use App\Services\Ocr\RegexFieldRecognizer;
use Tests\TestCase;

class RegexFieldRecognizerTest extends TestCase
{
    public function test_extracts_ic_and_name_from_clean_text(): void
    {
        $text = "No. K/P: 800315015678\nNama Pemohon: Ahmad Bin Ali\nDaerah: Johor Bahru";

        $result = (new RegexFieldRecognizer)->recognize($text);

        $this->assertSame('800315015678', $result['fields']['ic']['value'] ?? null);
        $this->assertSame('Ahmad Bin Ali', $result['fields']['nama']['value'] ?? null);
        $this->assertSame('Johor Bahru', $result['fields']['daerah']['value'] ?? null);
    }

    public function test_fuzzy_label_match_tolerates_ocr_typos(): void
    {
        $text = "Nma Pemohon: Ahmad Bin Ali\nNo K/P: 800315015678";

        $result = (new RegexFieldRecognizer)->recognize($text);

        $this->assertSame('Ahmad Bin Ali', $result['fields']['nama']['value'] ?? null);
        $this->assertSame('800315015678', $result['fields']['ic']['value'] ?? null);
    }

    public function test_extracts_household_member_table(): void
    {
        $text = <<<'TEXT'
MAKLUMAT ISI RUMAH
Nama IC Hubungan Umur Pekerjaan
Ahmad Bin Ali 800315015678 Suami 46 Pekerja Am
Siti Binti Abu 820415015234 Isteri 44 Suri Rumah
TEXT;

        $result = (new RegexFieldRecognizer)->recognize($text);

        $members = $result['household_members'] ?? [];

        $this->assertCount(2, $members);
        $this->assertSame('Ahmad Bin Ali', $members[0]['nama'] ?? null);
        $this->assertSame('800315015678', $members[0]['ic'] ?? null);
        $this->assertSame('Suami', $members[0]['hubungan'] ?? null);
        $this->assertSame(46, $members[0]['umur'] ?? null);
        $this->assertSame('820415015234', $members[1]['ic'] ?? null);
    }

    public function test_invalid_ic_is_downrated(): void
    {
        $text = "No. K/P: 1234\nNama Pemohon: Ahmad Bin Ali";

        $result = (new RegexFieldRecognizer)->recognize($text);

        $this->assertLessThan(50, $result['fields']['ic']['confidence'] ?? 100);
    }
}
