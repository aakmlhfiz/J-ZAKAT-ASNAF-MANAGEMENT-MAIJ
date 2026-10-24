<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Normalises raw OCR values (IC, phone, money, dates, postcodes) while always
 * preserving the original OCR text. Unknown/ambiguous values (e.g. "S.D.A")
 * are preserved verbatim — never guessed or blanked.
 */
class ValueNormalizer
{
    /**
     * @return array{value:string|null, original_value:string|null, normalized_value:string|null}
     */
    public function normalize(string $field, ?string $raw, string $type = 'text'): array
    {
        $original = $raw !== null ? trim($raw) : null;

        if ($original === null || $original === '' || $original === '-' || $original === '—') {
            return [
                'value' => null,
                'original_value' => ($original === null || $original === '') ? null : $original,
                'normalized_value' => null,
            ];
        }

        $normalized = match ($type) {
            'ic' => $this->ic($original),
            'phone' => $this->phone($original),
            'poskod' => $this->poskod($original),
            'amount' => $this->amount($original),
            'number' => $this->number($original),
            'date' => $this->date($original),
            default => $this->text($original),
        };

        // Typed fields must produce a valid normalised value — never pass the
        // raw OCR garbage through when it fails validation. Free-text fields
        // fall back to the cleaned original.
        $isTyped = in_array($type, ['ic', 'phone', 'poskod', 'amount', 'number', 'date'], true);

        if ($normalized === null || $normalized === '') {
            $final = $isTyped ? null : $original;
        } else {
            $final = $normalized;
        }

        return [
            'value' => $final,
            'original_value' => $isTyped && $final === null ? null : $original,
            'normalized_value' => $final === $original || $final === null ? null : $final,
        ];
    }

    private function ic(string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) === 12) {
            return $digits;
        }

        if (preg_match('/\d{6}[\s\-]?\d{2}[\s\-]?\d{4}/', $value, $m)) {
            return preg_replace('/\D/', '', $m[0]);
        }

        if (strlen($digits) >= 10) {
            return substr($digits, 0, 12);
        }

        return null;
    }

    private function phone(string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) < 6) {
            return null;
        }

        // Malaysian numbers start with 0; OCR often drops the leading zero of a
        // 9-digit mobile (e.g. "192334003" → "0192334003"). A 10-digit value
        // without the leading 0 is ambiguous (possibly an extra digit), so we
        // leave it verbatim and let the confidence fallback flag it for review.
        if (strlen($digits) === 9 && $digits[0] !== '0') {
            $digits = '0'.$digits;
        }

        if (strlen($digits) <= 12) {
            return $digits;
        }

        return substr($digits, 0, 12);
    }

    private function poskod(string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value);

        return strlen($digits) >= 5 ? substr($digits, 0, 5) : null;
    }

    private function amount(string $value): ?string
    {
        // Strip thousands separators but keep decimal point.
        $cleaned = preg_replace('/[^\d.,]/', '', $value);
        $cleaned = str_replace(',', '', $cleaned);

        if ($cleaned === '' || ! is_numeric($cleaned)) {
            return null;
        }

        return number_format((float) $cleaned, 2, '.', '');
    }

    private function number(string $value): ?string
    {
        $digits = preg_replace('/[^\d]/', '', $value);

        return $digits !== '' ? (string) ((int) $digits) : null;
    }

    private function date(string $value): ?string
    {
        $v = trim($value);

        if (preg_match('/^(\d{1,2})[\-\/\.](\d{1,2})[\-\/\.](\d{4})$/', $v, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})[\-\/\.](\d{1,2})[\-\/\.](\d{1,2})$/', $v, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Month-name dates ("Feb 2026", "Februari 2026") — keep readable form.
        if (preg_match('/^([A-Za-z]{3,9})\.?\s+(\d{4})$/', $v, $m)) {
            $months = [
                'jan' => '01', 'januari' => '01', 'feb' => '02', 'februari' => '02',
                'mar' => '03', 'mac' => '03', 'apr' => '04', 'april' => '04',
                'may' => '05', 'mei' => '05', 'jun' => '06', 'june' => '06',
                'jul' => '07', 'july' => '07', 'aug' => '08', 'ogos' => '08',
                'sep' => '09', 'sept' => '09', 'okt' => '10', 'oct' => '10',
                'nov' => '11', 'dis' => '12', 'dec' => '12',
            ];
            $monthKey = strtolower($m[1]);
            $month = $months[$monthKey] ?? null;
            if ($month) {
                return $m[2].'-'.$month;
            }
        }

        return null;
    }

    private function text(string $value): ?string
    {
        $cleaned = preg_replace('/\s+/', ' ', $value);

        return $cleaned !== '' && $cleaned !== '-' ? trim($cleaned) : null;
    }
}
