<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Tri-state checkbox detection via ink-density probing.
 *
 * Each checkbox is rendered as a thin box; a tick adds a diagonal stroke which
 * measurably raises the dark-pixel ratio inside the box. We compare each box's
 * measured density against a per-position "empty" baseline (calibrated from the
 * blank template) or, when unavailable, against the group median. Returns
 * true/false/null per option, plus confidence and a needs_review flag. Never
 * infers a checkbox state from surrounding text.
 */
class CheckboxDetector
{
    /** Multi-select groups (an applicant may select several options). */
    private const MULTI_SELECT = [
        'bantuan' => true,
        'perubatan_jenis_rawatan' => true,
        'jenis_berdikari' => true,
        'harta_rumah' => true,
        'harta_tanah' => true,
        'harta_kereta' => true,
        'harta_motosikal' => true,
    ];

    private WordGrid $grid;

    private int $page;

    private array $group;

    /** @var callable|null */
    private $densityProvider;

    private array $fixedZones;

    private array $baselines;

    private bool $singleSelect;

    public function __construct(
        WordGrid $grid,
        int $page,
        array $group,
        ?callable $densityProvider = null,
        array $fixedZones = [],
        array $baselines = [],
    ) {
        $this->grid = $grid;
        $this->page = $page;
        $this->group = $group;
        $this->densityProvider = $densityProvider;
        $this->fixedZones = $fixedZones;
        $this->baselines = $baselines;
        $this->singleSelect = ! isset(self::MULTI_SELECT[$group['key'] ?? '']);
    }

    /**
     * @return array<string, array{value:bool|null, confidence:int, needs_review:bool}>
     */
    public function detect(): array
    {
        $groupKey = $this->group['key'] ?? 'unknown';
        $results = [];
        $densities = [];

        foreach (($this->group['options'] ?? []) as $optionKey => $option) {
            $zone = $this->boxZone($groupKey, $optionKey, $option);
            $density = $zone !== null && $this->densityProvider
                ? ($this->densityProvider)($zone['x'], $zone['y'], $zone['w'], $zone['h'], $this->page)
                : null;

            if ($density === null && $zone !== null) {
                $density = $this->glyphDensity($zone);
            }

            $densities[$optionKey] = $density;
            $results[$optionKey] = [
                'value' => null,
                'confidence' => 0,
                'needs_review' => true,
                'density' => $density,
            ];
        }

        $probed = array_filter($densities, fn ($d) => $d !== null);
        if (empty($probed)) {
            return array_map(fn ($r) => ['value' => null, 'confidence' => 0, 'needs_review' => true], $results);
        }

        // Per-option baseline (calibrated "empty" density, else group median).
        $groupMedian = $this->median($densities);
        $checked = [];

        foreach ($densities as $optionKey => $density) {
            if ($density === null) {
                $results[$optionKey] = ['value' => null, 'confidence' => 0, 'needs_review' => true];

                continue;
            }

            $baseline = $this->baselines[$groupKey.'.'.$optionKey] ?? $groupMedian;
            $threshold = $baseline + max(0.015, $baseline * 0.6);
            $isChecked = $density >= $threshold;
            $delta = $density - $baseline;

            if ($isChecked) {
                $checked[$optionKey] = true;
            }

            if ($isChecked) {
                $confidence = match (true) {
                    $delta >= 0.05 => 88,
                    $delta >= 0.03 => 75,
                    default => 62,
                };
            } else {
                $confidence = $baseline > 0.05 ? 55 : 78;
            }

            $results[$optionKey] = [
                'value' => $isChecked,
                'confidence' => $confidence,
                'needs_review' => $confidence < 70 || ($baseline > 0.05 && ! $isChecked),
                'density' => $density,
            ];
        }

        // A single-select group must have exactly one checked option.
        if ($this->singleSelect && count($checked) > 1) {
            foreach ($results as $key => &$result) {
                $result['value'] = null;
                $result['confidence'] = 0;
                $result['needs_review'] = true;
            }
        }

        return $results;
    }

    private function median(array $densities): float
    {
        $values = array_values(array_filter($densities, fn ($d) => $d !== null));
        if (empty($values)) {
            return 0;
        }
        sort($values);
        $n = count($values);
        $mid = intdiv($n, 2);

        return $n % 2 === 1 ? $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }

    /**
     * Expose the derived probe zone for every option (used for calibrating the
     * blank-template baselines).
     *
     * @return array<string, array{x:int,y:int,w:int,h:int}|null>
     */
    public function zones(): array
    {
        $groupKey = $this->group['key'] ?? 'unknown';
        $out = [];
        foreach (($this->group['options'] ?? []) as $optionKey => $option) {
            $out[$optionKey] = $this->boxZone($groupKey, $optionKey, $option);
        }

        return $out;
    }

    /**
     * Determine the probe rectangle for an option. Prefers a fixed zone
     * (calibrated box coordinates), otherwise anchors immediately to the left
     * of the option label word found within its y band.
     */
    private function boxZone(string $groupKey, string $optionKey, array $option): ?array
    {
        $fixedKey = $groupKey.'.'.$optionKey;
        if (isset($this->fixedZones[$fixedKey])) {
            $z = $this->fixedZones[$fixedKey];

            return ['x' => $z['x'], 'y' => $z['y'], 'w' => $z['w'], 'h' => $z['h']];
        }

        [$label, $yTop, $yBottom] = $option;
        $word = $this->grid->findLabel([$label], 100, $yTop, MaiFormTemplate::$referenceWidth - 100, $yBottom - $yTop);

        if ($word === null) {
            return null;
        }

        return [
            'x' => $word['l'] - 46,
            'y' => $word['t'] - 9,
            'w' => 40,
            'h' => 34,
        ];
    }

    /** Fallback when no pixel data: look for check-mark glyph tokens near the box. */
    private function glyphDensity(array $zone): ?float
    {
        $words = $this->grid->zoneWords($zone['x'] - 10, $zone['y'] - 10, $zone['w'] + 20, $zone['h'] + 20, 0);
        foreach ($words as $word) {
            if (preg_match('/[☑✓✔✗✘]/u', $word['text']) || preg_match('/^[xX]$/', $word['text'])) {
                return 0.2; // clearly marked
            }
        }

        return null;
    }
}
