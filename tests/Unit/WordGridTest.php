<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\InputTypeDetector;
use App\Services\Ocr\DocumentIntelligence\WordGrid;
use PHPUnit\Framework\TestCase;

class WordGridTest extends TestCase
{
    private function grid(): WordGrid
    {
        return new WordGrid([
            ['l' => 100, 't' => 100, 'w' => 80, 'h' => 30, 'conf' => 95, 'text' => 'Nama'],
            ['l' => 200, 't' => 100, 'w' => 120, 'h' => 30, 'conf' => 60, 'text' => 'Ali'],
            ['l' => 100, 't' => 200, 'w' => 90, 'h' => 30, 'conf' => 90, 'text' => 'IC:'],
            ['l' => 220, 't' => 200, 'w' => 130, 'h' => 30, 'conf' => 55, 'text' => '800315015678'],
        ], 1870, 2420);
    }

    public function test_zone_text_returns_joined_words(): void
    {
        $text = $this->grid()->zoneText(180, 90, 300, 60);

        $this->assertSame('Ali', $text);
    }

    public function test_find_label_matches_whole_word(): void
    {
        $found = $this->grid()->findLabel(['Nama']);

        $this->assertNotNull($found);
        $this->assertSame('Nama', $found['text']);
    }

    public function test_find_label_returns_null_when_absent(): void
    {
        $this->assertNull($this->grid()->findLabel(['Parlimen']));
    }

    public function test_words_after_anchor_on_same_row(): void
    {
        $anchor = $this->grid()->findLabel(['Nama']);
        $after = $this->grid()->wordsAfter($anchor);

        $this->assertCount(1, $after);
        $this->assertSame('Ali', $after[0]['text']);
    }

    public function test_normalises_words_into_reference_space(): void
    {
        // Page is rendered at half the reference width → words are doubled.
        $grid = new WordGrid([
            ['l' => 100, 't' => 100, 'w' => 40, 'h' => 20, 'conf' => 90, 'text' => 'X'],
        ], 935, 1210);

        $this->assertSame(200, $grid->words()[0]['l']);
        $this->assertSame(200, $grid->words()[0]['t']);
    }
}

class InputTypeDetectorTest extends TestCase
{
    private InputTypeDetector $d;

    protected function setUp(): void
    {
        parent::setUp();
        $this->d = new InputTypeDetector;
    }

    public function test_high_confidence_zone_is_printed(): void
    {
        $r = $this->d->classify([['text' => 'Ahmad', 'conf' => 92], ['text' => 'Ali', 'conf' => 95]], 'Ahmad Ali');

        $this->assertSame(InputTypeDetector::PRINTED, $r['input_type']);
    }

    public function test_mid_confidence_zone_is_typed(): void
    {
        $r = $this->d->classify([['text' => 'Ahmad', 'conf' => 70], ['text' => 'Ali', 'conf' => 74]], 'Ahmad Ali');

        $this->assertSame(InputTypeDetector::TYPED, $r['input_type']);
    }

    public function test_low_confidence_zone_is_handwritten(): void
    {
        $r = $this->d->classify([['text' => 'Ahmad', 'conf' => 40], ['text' => 'Ali', 'conf' => 45]], 'Ahmad Ali');

        $this->assertSame(InputTypeDetector::HANDWRITTEN, $r['input_type']);
    }

    public function test_empty_value_returns_null(): void
    {
        $r = $this->d->classify([['text' => 'x', 'conf' => 90]], null);

        $this->assertNull($r['input_type']);
    }
}
