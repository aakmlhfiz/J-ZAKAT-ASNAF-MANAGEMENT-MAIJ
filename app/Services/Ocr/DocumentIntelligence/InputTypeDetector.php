<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Heuristically classifies an extracted value as printed, typed, or handwritten
 * based on the mean per-word OCR confidence of the words inside the field zone.
 * Low confidence + irregular tokens strongly suggest handwriting; mid-range
 * confidence suggests typed text; high, uniform confidence suggests printed.
 */
class InputTypeDetector
{
    public const PRINTED = 'printed';

    public const TYPED = 'typed';

    public const HANDWRITTEN = 'handwritten';

    /**
     * @param  array<int, array{text:string,conf:float}>  $zoneWords
     * @return array{input_type:string|null, confidence:float}
     */
    public function classify(array $zoneWords, ?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return ['input_type' => null, 'confidence' => 0.0];
        }

        $confs = array_map(fn ($w) => (float) $w['conf'], $zoneWords);
        $confs = array_filter($confs, fn ($c) => $c > 0);

        if (empty($confs)) {
            // No word-level confidence available (e.g. text-layer fallback).
            return ['input_type' => self::TYPED, 'confidence' => 50.0];
        }

        $mean = array_sum($confs) / count($confs);

        if ($mean >= 85) {
            return ['input_type' => self::PRINTED, 'confidence' => min(95, $mean)];
        }

        if ($mean >= 60) {
            return ['input_type' => self::TYPED, 'confidence' => $mean];
        }

        return ['input_type' => self::HANDWRITTEN, 'confidence' => max(35, $mean)];
    }
}
