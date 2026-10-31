<?php

namespace App\Services\Document\Extractors;

use App\Services\Document\DocumentConfidenceResolver;
use App\Services\Ocr\DocumentIntelligence\ValueNormalizer;

abstract class AbstractDocumentExtractor implements DocumentExtractor
{
    protected ValueNormalizer $normalizer;

    protected int $confidenceThreshold;

    public function __construct(?ValueNormalizer $normalizer = null)
    {
        $this->normalizer = $normalizer ?? new ValueNormalizer;
        $this->confidenceThreshold = (int) config('ocr.confidence_threshold', 70);
    }

    /**
     * @return array{fields:array<string, array<string, mixed>>, warnings:array<int, string>, meta:array<string, mixed>}
     */
    protected function result(array $fields, array $warnings = [], array $meta = []): array
    {
        return [
            'fields' => $fields,
            'warnings' => array_values(array_unique($warnings)),
            'meta' => $meta,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function makeField(
        string $key,
        ?string $raw,
        string $type,
        array $context,
        ?int $page = null,
        ?string $sourceLabel = null,
        array $warnings = [],
    ): array {
        $raw = $raw !== null ? trim($raw) : null;
        $normalized = null;
        $original = $raw;

        if ($raw !== null && $raw !== '') {
            $normalizeType = match ($type) {
                'amount' => 'amount',
                'date' => 'date',
                'integer' => 'number',
                'ic' => 'ic',
                'phone' => 'phone',
                'postcode' => 'poskod',
                default => 'text',
            };
            $normalized = $this->normalizer->normalize($key, $raw, $normalizeType);
            if ($normalized['value'] === null) {
                $warnings[] = __('document_ocr.warnings.invalid_value', ['field' => $this->label($key)]);
            }
        }

        $value = $normalized['value'] ?? null;
        /** @var DocumentConfidenceResolver|null $resolver */
        $resolver = $context['confidence_resolver'] ?? null;
        $confidence = $resolver?->resolve($value, $page) ?? [
            'confidence' => null,
            'confidence_source' => null,
            'confidence_page' => null,
        ];

        $needsReview = $value === null
            || $confidence['confidence'] === null
            || $confidence['confidence'] < $this->confidenceThreshold;

        return [
            'value' => $value,
            'original_value' => $original,
            'normalized_value' => $normalized['normalized_value'] ?? null,
            'confidence' => $confidence['confidence'],
            'confidence_source' => $confidence['confidence_source'],
            'confidence_page' => $confidence['confidence_page'] ?? $page,
            'needs_review' => $needsReview,
            'page' => $page,
            'source_label' => $sourceLabel,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    protected function label(string $key): string
    {
        return __('document_ocr.fields.'.$key);
    }

    /**
     * Find the value following a label. Handles "Label: value", value on the
     * next line, and multi-line addresses.
     *
     * @param  array<int, string>  $labels
     * @return array{value:string, page:int, label:string}|null
     */
    protected function findLabeled(string $text, array $labels, bool $multiLine = false): ?array
    {
        $lines = $this->lines($text);

        foreach ($labels as $label) {
            $needle = mb_strtolower(trim($label));
            if ($needle === '') {
                continue;
            }

            foreach ($lines as $index => $line) {
                $haystack = mb_strtolower($line['text']);
                $position = mb_strpos($haystack, $needle);
                if ($position === false) {
                    continue;
                }

                $after = trim(mb_substr($line['text'], $position + mb_strlen($label)));
                $after = ltrim($after, " \t:-–—=");

                if ($after !== '' && ! $this->isLikelyLabel($after)) {
                    return ['value' => $this->clean($after), 'page' => $line['page'], 'label' => $label];
                }

                $collected = [];
                $page = $line['page'];
                $max = $multiLine ? 4 : 1;
                for ($offset = 1; $offset <= $max; $offset++) {
                    $next = $lines[$index + $offset] ?? null;
                    if (! $next || $next['text'] === '' || $this->isLikelyLabel($next['text'])) {
                        break;
                    }
                    $collected[] = $next['text'];
                    $page = $next['page'];
                }

                if ($collected !== []) {
                    return ['value' => $this->clean(implode(' ', $collected)), 'page' => $page, 'label' => $label];
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $labels
     */
    protected function moneyAfter(string $text, array $labels): ?array
    {
        $found = $this->findLabeled($text, $labels);
        if ($found === null) {
            return null;
        }

        $amount = $this->firstAmount($found['value']);
        if ($amount === null) {
            return null;
        }

        return ['value' => $amount, 'page' => $found['page'], 'label' => $found['label']];
    }

    /**
     * @param  array<int, string>  $labels
     */
    protected function dateAfter(string $text, array $labels): ?array
    {
        $found = $this->findLabeled($text, $labels);
        if ($found === null) {
            return null;
        }

        $normalized = $this->normalizer->normalize('document_date', $found['value'], 'date');
        if ($normalized['value'] === null) {
            return null;
        }

        return ['value' => $normalized['value'], 'page' => $found['page'], 'label' => $found['label']];
    }

    protected function firstAmount(string $value): ?string
    {
        if (preg_match('/(?:RM|MYR)?\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/i', $value, $m)) {
            $normalized = $this->normalizer->normalize('amount', $m[1], 'amount');

            return $normalized['value'];
        }

        return null;
    }

    /**
     * @return array<int, array{text:string, page:int}>
     */
    protected function lines(string $text): array
    {
        $lines = [];
        $page = 1;
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            if (preg_match('/^---\s*PAGE\s+(\d+)\s*---$/i', trim($line), $m)) {
                $page = (int) $m[1];

                continue;
            }
            $lines[] = ['text' => trim($line), 'page' => $page];
        }

        return $lines;
    }

    protected function clean(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return trim($value, " \t:;,-–—|");
    }

    protected function isLikelyLabel(string $value): bool
    {
        $value = mb_strtolower(trim($value));

        if ($value === '' || mb_strlen($value) > 80) {
            return false;
        }

        return (bool) preg_match('/^(nama|no\.?\s*(kad|ic|kp)|alamat|jawatan|majikan|gaji|elaun|potongan|jumlah|tarikh|tempoh|bank|akaun|penyewa|tuan rumah|pesakit|hospital|klinik|bapa|ibu|nama penuh|employee|employer|salary|address|position|date|amount|total|account|tenant|landlord|patient)\b/iu', $value);
    }
}
