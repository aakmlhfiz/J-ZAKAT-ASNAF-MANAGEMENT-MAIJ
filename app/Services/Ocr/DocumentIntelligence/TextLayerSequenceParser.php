<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Type-aware, anchor-based parser for label-less text layers (digital / vector
 * AcroForm MAIJ PDFs) where the page content does not rasterise and the only
 * signal is the filled values in visual reading order.
 *
 * Each page has a declarative matcher spec (see
 * {@see MaiFormTemplate::textLayerSpecs()}); the parser walks the token stream
 * and consumes tokens by pattern rather than by fixed index. Multi-token names,
 * grouped account numbers, dates ("Feb 2026") and 12-digit ICs are handled.
 * Unmatched values become null with needs_review — it never guesses.
 */
class TextLayerSequenceParser
{
    /** Relationship words used to delimit names. */
    private const RELATIONSHIPS = [
        'saudara', 'anak', 'isteri', 'suami', 'ibu', 'bapa', 'ayah', 'emak',
        'adik', 'kakak', 'abang', 'cucu', 'menantu', 'kawan', 'diri', 'penjaga',
        'sepupu', 'sedara', 'bapa saudara', 'mak saudara', 'bonda', 'ayahanda',
    ];

    /** Malay name particles (not delimiters). */
    private const NAME_PARTICLES = ['bin', 'binti', 'bt', 'bte', 'binte'];

    private const MONTHS = [
        'jan', 'januari', 'feb', 'februari', 'mar', 'mac', 'apr', 'april',
        'may', 'mei', 'jun', 'june', 'jul', 'july', 'aug', 'ogos',
        'sep', 'sept', 'okt', 'oct', 'nov', 'dis', 'dec',
    ];

    /**
     * Parse a page's text layer using its matcher spec.
     *
     * @return array<string, array{value:string|null, confidence:int, needs_review:bool}>
     */
    public function parse(string $text, int $page): array
    {
        $spec = MaiFormTemplate::textLayerSpecs()[$page] ?? null;
        if ($spec === null) {
            return [];
        }

        $tokens = preg_split('/\s+/', trim($text));
        $tokens = array_values(array_filter($tokens, fn ($t) => trim($t) !== ''));

        $results = [];
        $i = 0;

        foreach ($spec as $matcher) {
            $key = $matcher['key'];
            $value = null;
            $consumed = 0;
            $confidence = 0;

            if ($i < count($tokens)) {
                [$value, $consumed] = $this->match($matcher['type'], $tokens, $i, $matcher['max'] ?? null);
                if ($value !== null) {
                    $confidence = $this->confidenceFor($matcher['type']);
                }
            }

            $results[$key] = [
                'value' => $value,
                'confidence' => $confidence,
                'needs_review' => $value === null || $confidence < 60,
            ];

            $i += $consumed;
        }

        if ($i < count($tokens)) {
            $results['_unrecognized'] = [
                'value' => implode(' ', array_slice($tokens, $i)),
                'confidence' => 0,
                'needs_review' => true,
            ];
        }

        return $results;
    }

    /**
     * Consume tokens for a matcher type. Returns [value, consumed].
     */
    private function match(string $type, array $tokens, int $i, ?int $max = null): array
    {
        return match ($type) {
            'amount' => $this->matchAmount($tokens, $i),
            'account' => $this->matchAccount($tokens, $i, $max ?? 3),
            'name' => $this->matchName($tokens, $i),
            'relationship' => $this->matchRelationship($tokens, $i),
            'ic' => $this->matchIc($tokens, $i),
            'date' => $this->matchDate($tokens, $i),
            'text' => $this->matchText($tokens, $i, $max ?? 4),
            'code' => $this->matchCode($tokens, $i),
            default => [null, 0],
        };
    }

    private function matchAmount(array $tokens, int $i): array
    {
        $token = $tokens[$i] ?? null;
        if ($token !== null && preg_match('/^\d[\d,\.]*$/', $token)) {
            return [$token, 1];
        }

        return [null, 0];
    }

    /**
     * Account numbers are often grouped, e.g. "5555 5555". Consumes consecutive
     * numeric tokens until the combined digit count reaches a plausible account
     * length (≥ 6 digits) or the token cap is reached.
     */
    private function matchAccount(array $tokens, int $i, int $max = 3): array
    {
        $parts = [];
        $digits = 0;
        $j = $i;
        while ($j < count($tokens) && count($parts) < $max && $digits < 6 && preg_match('/^\d[\d,\.]*$/', $tokens[$j])) {
            $parts[] = $tokens[$j];
            $digits += strlen(preg_replace('/\D/', '', $tokens[$j]));
            $j++;
        }

        if (empty($parts)) {
            return [null, 0];
        }

        return [implode(' ', $parts), count($parts)];
    }

    private function matchName(array $tokens, int $i): array
    {
        $parts = [];
        $j = $i;
        while ($j < count($tokens)) {
            $token = $tokens[$j];
            if (! $this->isNameToken($token) || $this->isRelationship($token)) {
                break;
            }
            $parts[] = $token;
            $j++;
        }

        if (empty($parts)) {
            return [null, 0];
        }

        return [implode(' ', $parts), count($parts)];
    }

    private function matchRelationship(array $tokens, int $i): array
    {
        $token = $tokens[$i] ?? null;
        if ($token !== null && $this->isRelationship($token)) {
            return [$token, 1];
        }

        return [null, 0];
    }

    private function matchIc(array $tokens, int $i): array
    {
        $token = $tokens[$i] ?? null;
        if ($token !== null && preg_match('/^\d{10,12}$/', $token)) {
            return [substr($token, 0, 12), 1];
        }

        return [null, 0];
    }

    private function matchDate(array $tokens, int $i): array
    {
        $token = $tokens[$i] ?? null;
        if ($token === null) {
            return [null, 0];
        }

        // dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy
        if (preg_match('/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4}$/', $token)) {
            return [$token, 1];
        }

        // Month-name + year ("Feb 2026")
        $next = $tokens[$i + 1] ?? null;
        if ($this->isMonth($token) && $next !== null && preg_match('/^\d{4}$/', $next)) {
            return [$token.' '.$next, 2];
        }

        return [null, 0];
    }

    private function matchText(array $tokens, int $i, int $max): array
    {
        $parts = [];
        $j = $i;
        while ($j < count($tokens) && count($parts) < $max) {
            $token = $tokens[$j];
            if ($this->isNumber($token) || $this->isDateStart($token) || $this->isRelationship($token)) {
                break;
            }
            $parts[] = $token;
            $j++;
        }

        if (empty($parts)) {
            return [null, 0];
        }

        return [implode(' ', $parts), count($parts)];
    }

    private function matchCode(array $tokens, int $i): array
    {
        $token = $tokens[$i] ?? null;
        if ($token !== null && preg_match('/^[A-Za-z0-9][A-Za-z0-9\/\-\.,]*$/', $token)) {
            return [$token, 1];
        }

        return [null, 0];
    }

    private function confidenceFor(string $type): int
    {
        return match ($type) {
            'ic' => 70,
            'relationship', 'date' => 66,
            'amount', 'code' => 64,
            'account' => 62,
            'name' => 58,
            'text' => 55,
            default => 50,
        };
    }

    private function isNameToken(string $token): bool
    {
        if (in_array(mb_strtolower($token), self::NAME_PARTICLES, true)) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]+([.\'-][A-Za-z]+)*$/', $token);
    }

    private function isRelationship(string $token): bool
    {
        return in_array(mb_strtolower($token), self::RELATIONSHIPS, true);
    }

    private function isMonth(string $token): bool
    {
        return in_array(mb_strtolower(rtrim($token, '.')), self::MONTHS, true);
    }

    private function isNumber(string $token): bool
    {
        return (bool) preg_match('/^\d[\d,\.]*$/', $token);
    }

    private function isDateStart(string $token): bool
    {
        if ($this->isMonth($token)) {
            return true;
        }

        return (bool) preg_match('/^\d{1,2}[\/\-\.]\d{1,2}/', $token);
    }
}
