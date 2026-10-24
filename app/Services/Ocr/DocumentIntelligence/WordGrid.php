<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * A searchable 2D grid of OCR'd words with bounding boxes.
 *
 * Words are normalised into a reference coordinate space (see
 * {@see MaiFormTemplate::$referenceWidth} / height) so template zones defined
 * in that space match regardless of the source scan resolution. All template
 * zone queries operate in the reference space.
 */
class WordGrid
{
    /** @var array<int, array{l:int,t:int,w:int,h:int,conf:float,text:string}> */
    private array $words = [];

    private float $scaleX = 1.0;

    private float $scaleY = 1.0;

    public function __construct(array $words = [], int $pageWidth = 0, int $pageHeight = 0)
    {
        $refW = MaiFormTemplate::$referenceWidth;
        $refH = MaiFormTemplate::$referenceHeight;

        if ($pageWidth > 0) {
            $this->scaleX = $refW / $pageWidth;
        }
        if ($pageHeight > 0) {
            $this->scaleY = $refH / $pageHeight;
        }

        foreach ($words as $w) {
            if (trim((string) ($w['text'] ?? '')) === '') {
                continue;
            }
            $this->words[] = [
                'l' => (int) round(($w['l'] ?? 0) * $this->scaleX),
                't' => (int) round(($w['t'] ?? 0) * $this->scaleY),
                'w' => (int) round(($w['w'] ?? 0) * $this->scaleX),
                'h' => (int) round(($w['h'] ?? 0) * $this->scaleY),
                'conf' => (float) ($w['conf'] ?? 0),
                'text' => (string) $w['text'],
            ];
        }

        usort($this->words, fn ($a, $b) => $a['t'] <=> $b['t'] ?: $a['l'] <=> $b['l']);
    }

    public function count(): int
    {
        return count($this->words);
    }

    public function isEmpty(): bool
    {
        return count($this->words) === 0;
    }

    /** All words in reading order. */
    public function words(): array
    {
        return $this->words;
    }

    public function plainText(): string
    {
        return implode(' ', array_map(fn ($w) => $w['text'], $this->words));
    }

    /**
     * Words whose centre falls inside the given reference-space rectangle.
     * Optionally filters to words with a minimum per-word confidence.
     */
    public function zoneWords(int $x, int $y, int $w, int $h, float $minConf = 0): array
    {
        $x1 = $x;
        $y1 = $y;
        $x2 = $x + $w;
        $y2 = $y + $h;

        return array_values(array_filter(
            $this->words,
            fn ($word) => $this->inZone($word, $x1, $y1, $x2, $y2) && $word['conf'] >= $minConf
        ));
    }

    /** Joined text of words inside a zone. */
    public function zoneText(int $x, int $y, int $w, int $h, float $minConf = 0): ?string
    {
        $words = $this->zoneWords($x, $y, $w, $h, $minConf);
        if (empty($words)) {
            return null;
        }
        usort($words, fn ($a, $b) => $a['l'] <=> $b['l']);

        return trim(implode(' ', array_map(fn ($word) => $word['text'], $words)));
    }

    /**
     * Find the first word matching any of the given label variants, optionally
     * restricted to a search region. Whole-word match first (case-insensitive),
     * then substring match for longer labels. Returns word data or null.
     */
    public function findLabel(array $variants, ?int $x = null, ?int $y = null, ?int $w = null, ?int $h = null): ?array
    {
        $candidates = $this->words;
        if ($x !== null && $w !== null && $y !== null && $h !== null) {
            $x1 = $x;
            $y1 = $y;
            $x2 = $x + $w;
            $y2 = $y + $h;
            $candidates = array_values(array_filter($candidates, fn ($word) => $this->inZone($word, $x1, $y1, $x2, $y2)));
        }

        foreach ($candidates as $word) {
            foreach ($variants as $variant) {
                $v = trim((string) $variant);
                if ($v === '') {
                    continue;
                }
                if (mb_strtolower($word['text']) === mb_strtolower($v)) {
                    return $word;
                }
            }
        }

        foreach ($candidates as $word) {
            foreach ($variants as $variant) {
                $v = trim((string) $variant);
                if (mb_strlen($v) < 6) {
                    continue;
                }
                if (mb_stripos($word['text'], $v) !== false) {
                    return $word;
                }
            }
        }

        return null;
    }

    /**
     * Words on the same visual line as the given anchor word (within a row
     * tolerance), positioned after (to the right of) its x position.
     */
    public function wordsAfter(array $anchor, float $rowTol = 0.35, ?int $maxRight = null): array
    {
        $rowTop = $anchor['t'] - (int) round($anchor['h'] * $rowTol);
        $rowBottom = $anchor['t'] + (int) round($anchor['h'] * (1 + $rowTol));
        $left = $anchor['l'] + $anchor['w'];

        return array_values(array_filter(
            $this->words,
            fn ($word) => $word['l'] >= $left
                && $word['t'] >= $rowTop && $word['t'] <= $rowBottom
                && ($maxRight === null || $word['l'] <= $maxRight)
        ));
    }

    private function inZone(array $word, int $x1, int $y1, int $x2, int $y2): bool
    {
        $cx = $word['l'] + (int) round($word['w'] / 2);
        $cy = $word['t'] + (int) round($word['h'] / 2);

        return $cx >= $x1 && $cx <= $x2 && $cy >= $y1 && $cy <= $y2;
    }
}
