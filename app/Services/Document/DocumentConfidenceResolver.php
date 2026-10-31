<?php

namespace App\Services\Document;

/**
 * Resolves field confidence from real Tesseract word-level confidence (TSV).
 * When no word coordinates/confidence are available (for example a PDF text
 * layer), returns null instead of inventing a score.
 */
class DocumentConfidenceResolver
{
    /** @var array<int, array<int, array{text:string, conf:float}>> */
    private array $pages = [];

    public function __construct(array $wordPages = [])
    {
        foreach ($wordPages as $index => $page) {
            $words = [];
            foreach (($page['words'] ?? []) as $word) {
                $text = trim((string) ($word['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $words[] = [
                    'text' => $text,
                    'conf' => (float) ($word['conf'] ?? 0),
                ];
            }
            $this->pages[(int) ($page['page'] ?? ($index + 1))] = $words;
        }
    }

    public function available(): bool
    {
        return $this->pages !== [];
    }

    /**
     * @return array{confidence:float|null, confidence_source:string|null, confidence_page:int|null}
     */
    public function resolve(?string $value, ?int $page = null): array
    {
        if (! $this->available() || $value === null || trim($value) === '') {
            return ['confidence' => null, 'confidence_source' => null, 'confidence_page' => null];
        }

        $target = $this->tokens($value);
        if ($target === []) {
            return ['confidence' => null, 'confidence_source' => null, 'confidence_page' => null];
        }

        $candidates = $page !== null && isset($this->pages[$page])
            ? [$page => $this->pages[$page]]
            : $this->pages;

        foreach ($candidates as $pageNumber => $words) {
            $conf = $this->match($target, $words);
            if ($conf !== null) {
                return [
                    'confidence' => round($conf, 1),
                    'confidence_source' => 'tesseract_tsv',
                    'confidence_page' => (int) $pageNumber,
                ];
            }
        }

        // A short value (IC, postcode, amount) may be split by OCR punctuation.
        // Only accept a token-level match when every meaningful token is found.
        if (count($target) <= 2) {
            foreach ($candidates as $pageNumber => $words) {
                $values = array_map(fn ($word) => $this->normalize($word['text']), $words);
                $matches = [];
                foreach ($target as $token) {
                    $found = false;
                    foreach ($words as $i => $word) {
                        if ($values[$i] === $token) {
                            $matches[] = $word['conf'];
                            $found = true;
                            break;
                        }
                    }
                    if (! $found) {
                        $matches = [];

                        break;
                    }
                }
                if ($matches !== []) {
                    return [
                        'confidence' => round(array_sum($matches) / count($matches), 1),
                        'confidence_source' => 'tesseract_tsv',
                        'confidence_page' => (int) $pageNumber,
                    ];
                }
            }
        }

        return ['confidence' => null, 'confidence_source' => null, 'confidence_page' => null];
    }

    private function match(array $target, array $words): ?float
    {
        $values = array_map(fn ($word) => $this->normalize($word['text']), $words);
        $targetCount = count($target);
        $limit = count($values) - $targetCount;

        for ($i = 0; $i <= $limit; $i++) {
            $slice = array_slice($values, $i, $targetCount);
            if ($slice !== $target) {
                continue;
            }

            $confidences = [];
            for ($j = 0; $j < $targetCount; $j++) {
                $confidences[] = $words[$i + $j]['conf'];
            }

            return array_sum($confidences) / count($confidences);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $value): array
    {
        // Numeric values (amounts, ICs, postcodes, dates) may be printed with
        // punctuation that OCR splits into several words. Match them as one
        // digit token so confidence can still be reported.
        if (preg_match('/^[\d\s,.\-\/]+$/u', trim($value))) {
            $digits = preg_replace('/[^0-9]/', '', $value);

            return $digits !== '' ? [$digits] : [];
        }

        $normalized = preg_replace('/[^a-z0-9]+/u', ' ', mb_strtolower($value));
        $parts = preg_split('/\s+/', trim($normalized)) ?: [];

        return array_values(array_filter($parts, fn ($part) => $part !== ''));
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]+/u', '', mb_strtolower($value)) ?? '';
    }
}
