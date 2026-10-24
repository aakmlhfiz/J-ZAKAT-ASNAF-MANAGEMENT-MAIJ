<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\CheckboxDetector;
use App\Services\Ocr\DocumentIntelligence\WordGrid;
use PHPUnit\Framework\TestCase;

class CheckboxDetectorTest extends TestCase
{
    private function grid(array $words): WordGrid
    {
        return new WordGrid($words, 1870, 2420);
    }

    private function group(array $options, string $key = 'kesihatan'): array
    {
        return ['key' => $key, 'page' => 1, 'options' => $options];
    }

    public function test_checked_option_is_detected_against_calibrated_baseline(): void
    {
        $group = $this->group([
            'sihat' => ['Sihat', 2075, 2140],
            'sakit' => ['Sakit', 2075, 2140],
            'cacat' => ['Cacat', 2075, 2140],
        ]);
        $grid = $this->grid([
            ['l' => 354, 't' => 2113, 'w' => 60, 'h' => 30, 'conf' => 90, 'text' => 'Sihat'],
            ['l' => 641, 't' => 2109, 'w' => 60, 'h' => 30, 'conf' => 90, 'text' => 'Sakit'],
            ['l' => 965, 't' => 2114, 'w' => 70, 'h' => 30, 'conf' => 90, 'text' => 'Cacat'],
        ]);

        $densities = [
            // [x,y,w,h,page] => density  (only Sihat is checked: 0.06 vs baseline 0.0044)
            '308,2104,40,34' => 0.06,
            '595,2100,40,34' => 0.0147,
            '919,2105,40,34' => 0.0228,
        ];
        $density = function ($x, $y, $w, $h, $page) use ($densities) {
            return $densities["{$x},{$y},{$w},{$h}"] ?? null;
        };

        $baselines = ['kesihatan.sihat' => 0.0044, 'kesihatan.sakit' => 0.0147, 'kesihatan.cacat' => 0.0228];
        $detector = new CheckboxDetector($grid, 1, $group, $density, [], $baselines);
        $result = $detector->detect();

        $this->assertTrue($result['sihat']['value']);
        $this->assertFalse($result['sakit']['value']);
        $this->assertFalse($result['cacat']['value']);
    }

    public function test_ambiguous_multiple_checked_single_select_returns_null(): void
    {
        $group = $this->group([
            'bujang' => ['Bujang', 2145, 2210],
            'berkahwin' => ['Berkahwin', 2145, 2210],
            'janda' => ['Janda', 2145, 2210],
            'duda' => ['Duda', 2145, 2210],
        ], 'status_perkahwinan');
        $grid = $this->grid([
            ['l' => 355, 't' => 2180, 'w' => 80, 'h' => 30, 'conf' => 90, 'text' => 'Bujang'],
            ['l' => 642, 't' => 2179, 'w' => 110, 'h' => 30, 'conf' => 90, 'text' => 'Berkahwin'],
            ['l' => 964, 't' => 2178, 'w' => 60, 'h' => 30, 'conf' => 90, 'text' => 'Janda'],
            ['l' => 1053, 't' => 2178, 'w' => 60, 'h' => 30, 'conf' => 90, 'text' => 'Duda'],
        ]);

        // Both bujang and duda appear clearly checked → ambiguous → null.
        $densities = [
            '309,2171,40,34' => 0.06,
            '596,2170,40,34' => 0.0037,
            '918,2169,40,34' => 0.0243,
            '1007,2169,40,34' => 0.15,
        ];
        $density = fn ($x, $y, $w, $h, $page) => $densities["{$x},{$y},{$w},{$h}"] ?? null;

        $baselines = [
            'status_perkahwinan.bujang' => 0.0022,
            'status_perkahwinan.berkahwin' => 0.0037,
            'status_perkahwinan.janda' => 0.0243,
            'status_perkahwinan.duda' => 0.0801,
        ];
        $detector = new CheckboxDetector($grid, 1, $group, $density, [], $baselines);
        $result = $detector->detect();

        foreach ($result as $r) {
            $this->assertNull($r['value']);
            $this->assertTrue($r['needs_review']);
        }
    }

    public function test_unreadable_checkbox_returns_null_with_needs_review(): void
    {
        $group = $this->group(['sihat' => ['Sihat', 2075, 2140]]);
        $grid = $this->grid([['l' => 354, 't' => 2113, 'w' => 60, 'h' => 30, 'conf' => 90, 'text' => 'Sihat']]);

        // No density provider → no pixel data.
        $detector = new CheckboxDetector($grid, 1, $group, null);
        $result = $detector->detect();

        $this->assertNull($result['sihat']['value']);
        $this->assertTrue($result['sihat']['needs_review']);
    }

    public function test_missing_label_returns_null_option(): void
    {
        $group = $this->group(['sakit' => ['Sakit', 2075, 2140]]);
        $grid = $this->grid([]);
        $density = fn () => 0.5;

        $detector = new CheckboxDetector($grid, 1, $group, $density);
        $result = $detector->detect();

        $this->assertNull($result['sakit']['value']);
        $this->assertTrue($result['sakit']['needs_review']);
    }
}
