<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision;

use App\Services\Ocr\DocumentIntelligence\FinancialAudit;
use App\Services\Ocr\DocumentIntelligence\ValueNormalizer;
use Throwable;

/**
 * Vision-AI document understanding for the MAIJ application form.
 *
 * Renders each page to an image, asks a vision model (OpenAI / Gemini / Ollama /
 * mock) to understand the full form layout (field label → value, handwriting,
 * checkboxes, tables, signatures, dates), then normalises the returned JSON into
 * the canonical `ocr_result` structure — the same contract as the local
 * template-anchored pipeline — so the review UI and import flow are unchanged.
 */
class VisionFormUnderstanding
{
    private ValueNormalizer $normalizer;

    private int $confidenceThreshold;

    public function __construct(?ValueNormalizer $normalizer = null, private array $mockFixture = [])
    {
        $this->normalizer = $normalizer ?? new ValueNormalizer;
        $this->confidenceThreshold = (int) config('ocr.vision.confidence_threshold', 60);
    }

    /** Whether the vision path is enabled and its provider is usable. */
    public function supported(): bool
    {
        if (! (bool) config('ocr.vision.enabled', false)) {
            return false;
        }

        try {
            $provider = VisionProviderFactory::resolve(config('ocr.vision.provider', 'openai'), $this->mockFixture);

            return $provider::isConfigured();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Run vision understanding. Returns the canonical ocr_result array, or null
     * when the pipeline cannot run (so the caller can fall back to local OCR).
     */
    public function understand(string $filePath, string $mimeType, string $ocrText, array $pageInfo): ?array
    {
        if (! $this->supported()) {
            return null;
        }

        $renderer = new PageImageRenderer;
        $pages = $renderer->render($filePath, $mimeType);

        if (empty($pages)) {
            return null;
        }

        $provider = VisionProviderFactory::resolve(config('ocr.vision.provider', 'openai'), $this->mockFixture);
        $prompt = (new MaiFormVisionSchema)->build();

        try {
            $raw = $provider->understand($pages, $prompt);
        } catch (Throwable $e) {
            logger()->error('Vision understanding threw, falling back to local pipeline', [
                'provider' => $provider->name(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! is_array($raw) || empty($raw)) {
            logger()->warning('Vision understanding returned no usable JSON', [
                'provider' => $provider->name(),
            ]);

            return null;
        }

        return $this->normalize($raw, count($pages), $provider->name());
    }

    /**
     * Coerce the model's JSON into the canonical ocr_result shape.
     *
     * @return array<string, mixed>
     */
    public function normalize(array $raw, int $pageCount, string $providerName): array
    {
        $fields = [];
        $sections = [];

        foreach (MaiFormVisionSchema::sections() as $section => $fieldMap) {
            $sectionData = is_array($raw[$section] ?? null) ? $raw[$section] : [];
            $sectionOut = [];

            foreach (array_keys($fieldMap) as $fieldKey) {
                $entry = $sectionData[$fieldKey] ?? null;
                $normalized = $this->normalizeField($fieldKey, $entry);
                $fields[$fieldKey] = $normalized;
                $sectionOut[$fieldKey] = $normalized;
            }

            $sections[$section] = $sectionOut;
        }

        $checkboxes = $this->normalizeCheckboxes($raw['checkboxes'] ?? []);
        $applications = $this->normalizeApplications($raw['applications'] ?? []);
        $dependents = $this->normalizeDependents($raw['dependents'] ?? []);
        $income = $this->normalizeAmountRows($raw['income'] ?? [], MaiFormVisionSchema::sections()['income']);
        $expenses = $this->normalizeAmountRows($raw['expenses'] ?? [], MaiFormVisionSchema::sections()['expenses']);

        $declaration = array_merge($sections['declaration'] ?? [], [
            'signature' => $this->normalizePresence($raw['declaration']['signature'] ?? []),
        ]);
        $verification = array_merge($sections['verification'] ?? [], [
            'stamp' => $this->normalizePresence($raw['verification']['stamp'] ?? []),
        ]);

        $financialAudit = (new FinancialAudit)->audit([
            'income' => $income,
            'expenses' => $expenses,
            'applicant' => $sections['applicant'] ?? [],
        ]);

        $sectionsDetected = [];
        if (is_array($raw['document']['sections_detected'] ?? null)) {
            foreach ($raw['document']['sections_detected'] as $title) {
                $title = (string) $title;
                $sectionsDetected[] = [
                    'number' => $this->sectionNumber($title),
                    'title' => $this->sectionTitle($title),
                    'page' => $this->sectionPage($title),
                ];
            }
        }

        return [
            'source' => 'vision',
            'provider' => $providerName,
            'document' => [
                'pages' => $pageCount,
                'document_type' => MaiFormVisionSchema::DOCUMENT_TYPE,
                'sections_detected' => $sectionsDetected,
            ],
            'fields' => $fields,
            'checkboxes' => $checkboxes,
            'applicant' => $sections['applicant'] ?? [],
            'applications' => $applications,
            'sara_hidup' => $sections['sara_hidup'] ?? [],
            'medical' => $sections['medical'] ?? [],
            'business' => $sections['business'] ?? [],
            'housing' => $sections['housing'] ?? [],
            'debt' => $sections['debt'] ?? [],
            'dependents' => $dependents,
            'residence' => $sections['residence'] ?? [],
            'assets' => $sections['assets'] ?? [],
            'employment' => $sections['employment'] ?? [],
            'income' => $income,
            'expenses' => $expenses,
            'financial_audit' => $financialAudit,
            'declaration' => $declaration,
            'verification' => $verification,
        ];
    }

    private function normalizeField(string $fieldKey, mixed $entry): array
    {
        $threshold = $this->confidenceThreshold;

        if (! is_array($entry)) {
            $value = is_scalar($entry) ? (string) $entry : null;

            return [
                'value' => $value,
                'original_value' => $value,
                'normalized_value' => null,
                'confidence' => $value !== null ? 50 : 0,
                'page' => null,
                'bbox' => null,
                'input_type' => null,
                'needs_review' => $value === null || $threshold > 50,
                'source_type' => 'vision',
            ];
        }

        $rawValue = isset($entry['value']) && $entry['value'] !== null ? (string) $entry['value'] : null;
        $type = MaiFormVisionSchema::fieldTypes()[$fieldKey] ?? 'text';
        $norm = $this->normalizer->normalize($fieldKey, $rawValue, $type);

        $confidence = (int) ($entry['confidence'] ?? 0);
        $confidence = max(0, min(100, $confidence));
        $inputType = $this->validInputType($entry['input_type'] ?? null);

        return [
            'value' => $norm['value'],
            'original_value' => $norm['original_value'],
            'normalized_value' => $norm['normalized_value'],
            'confidence' => $confidence,
            'page' => isset($entry['page']) ? (int) $entry['page'] : null,
            'bbox' => $this->validBbox($entry['bbox'] ?? null),
            'input_type' => $inputType,
            'needs_review' => $norm['value'] === null || $confidence < $threshold,
            'source_type' => 'vision',
        ];
    }

    private function normalizeCheckboxes(array $raw): array
    {
        $out = [];
        foreach (MaiFormVisionSchema::checkboxGroups() as $group => $options) {
            $groupData = is_array($raw[$group] ?? null) ? $raw[$group] : [];
            $out[$group] = [];
            foreach ($options as $option) {
                $state = $groupData[$option] ?? null;
                $bool = is_bool($state) ? $state : (is_int($state) && $state === 1 ? true : (is_int($state) && $state === 0 ? false : null));
                $out[$group][$option] = [
                    'value' => $bool,
                    'confidence' => $bool === null ? 0 : 70,
                    'needs_review' => $bool === null,
                ];
            }
        }

        return $out;
    }

    private function normalizeApplications(array $raw): array
    {
        if (! is_array($raw) || empty($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $app) {
            if (! is_array($app)) {
                continue;
            }
            $amount = $app['amount_requested'] ?? null;
            $amountNorm = $this->normalizer->normalize('amount', $amount !== null ? (string) $amount : null, 'amount');
            $selected = isset($app['selected']) ? filter_var($app['selected'], FILTER_VALIDATE_BOOLEAN) : null;

            $out[] = [
                'type' => isset($app['type']) ? (string) $app['type'] : null,
                'field' => 'bantuan_'.$this->bantuanSlug((string) ($app['type'] ?? '')),
                'selected' => $selected,
                'amount_requested' => $amountNorm['value'],
                'reason' => isset($app['reason']) && $app['reason'] !== null ? (string) $app['reason'] : null,
                'selection_source' => 'vision',
                'page' => isset($app['page']) ? (int) $app['page'] : 1,
                'needs_review' => $selected === null,
            ];
        }

        return $out;
    }

    private function normalizeDependents(array $raw): array
    {
        $rows = [];
        $rawRows = is_array($raw) ? array_values($raw) : [];

        foreach ($rawRows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $pend = $row['pendapatan'] ?? null;
            $pendNum = $pend !== null && is_numeric($pend) ? (int) $pend : null;
            $nama = isset($row['nama_penuh']) ? (string) $row['nama_penuh'] : null;
            $rows[] = [
                'row' => isset($row['row']) ? (int) $row['row'] : $i + 1,
                'nama_penuh' => $nama ?: null,
                'no_ic' => $this->normalizer->normalize('ic', isset($row['no_ic']) ? (string) $row['no_ic'] : null, 'ic')['value'],
                'sekolah_pekerjaan' => isset($row['sekolah_pekerjaan']) ? (string) $row['sekolah_pekerjaan'] : null,
                'pendapatan' => $pendNum,
                'hubungan' => isset($row['hubungan']) ? (string) $row['hubungan'] : null,
                'tinggal_bersama' => isset($row['tinggal_bersama']) ? (string) $row['tinggal_bersama'] : null,
                'confidence' => $nama ? 75 : 0,
                'needs_review' => $nama === null,
            ];
        }

        while (count($rows) < 6) {
            $rows[] = [
                'row' => count($rows) + 1,
                'nama_penuh' => null, 'no_ic' => null, 'sekolah_pekerjaan' => null,
                'pendapatan' => null, 'hubungan' => null, 'tinggal_bersama' => null,
                'confidence' => 0, 'needs_review' => false,
            ];
        }

        return $rows;
    }

    private function normalizeAmountRows(array $raw, array $fieldMap): array
    {
        $out = [];
        foreach ($fieldMap as $key => $label) {
            $entry = is_array($raw) ? ($raw[$key] ?? null) : null;
            $value = is_array($entry) ? ($entry['value'] ?? null) : $entry;
            $norm = $this->normalizer->normalize('amount', $value !== null ? (string) $value : null, 'amount');
            $confidence = is_array($entry) ? (int) ($entry['confidence'] ?? 0) : 0;

            $out[$key] = [
                'source' => $label,
                'value' => $norm['value'],
                'original_value' => $norm['original_value'],
                'confidence' => $norm['value'] !== null ? ($confidence ?: 70) : 0,
                'page' => is_array($entry) && isset($entry['page']) ? (int) $entry['page'] : 3,
                'bbox' => is_array($entry) ? $this->validBbox($entry['bbox'] ?? null) : null,
                'needs_review' => $norm['value'] === null,
            ];
        }

        return $out;
    }

    private function normalizePresence(array $raw): array
    {
        $present = isset($raw['present']) && $raw['present'] !== null
            ? filter_var($raw['present'], FILTER_VALIDATE_BOOLEAN)
            : null;
        $confidence = (int) ($raw['confidence'] ?? 0);

        return [
            'present' => $present,
            'confidence' => $confidence,
            'density' => null,
            'needs_review' => $present === null,
        ];
    }

    private function validInputType(mixed $value): ?string
    {
        return in_array($value, ['printed', 'typed', 'handwritten'], true) ? $value : null;
    }

    private function validBbox(mixed $bbox): ?array
    {
        if (! is_array($bbox)) {
            return null;
        }
        $keys = ['x', 'y', 'width', 'height'];
        foreach ($keys as $k) {
            if (! isset($bbox[$k]) || ! is_numeric($bbox[$k])) {
                return null;
            }
        }

        return [
            'x' => (int) $bbox['x'],
            'y' => (int) $bbox['y'],
            'width' => (int) $bbox['width'],
            'height' => (int) $bbox['height'],
        ];
    }

    private function bantuanSlug(string $type): string
    {
        $map = [
            'sara' => 'sara_hidup',
            'perubatan' => 'perubatan',
            'berdikari' => 'berdikari',
            'perumahan' => 'perumahan',
            'hutang' => 'hutang',
        ];
        $lower = mb_strtolower($type);
        foreach ($map as $needle => $slug) {
            if (mb_strpos($lower, $needle) !== false) {
                return $slug;
            }
        }

        return preg_replace('/[^a-z_]/', '', $lower) ?: 'unknown';
    }

    private function sectionNumber(string $title): string
    {
        return preg_match('/^\s*(\d+[A-E]?)\s*[\.\)]/', $title, $m) ? $m[1] : '';
    }

    private function sectionTitle(string $title): string
    {
        $clean = preg_replace('/^\s*\d+[A-E]?[\.\)]\s*/', '', $title);

        return $clean !== '' ? trim($clean) : trim($title);
    }

    private function sectionPage(string $title): int
    {
        $lower = mb_strtolower($title);
        if (mb_strpos($lower, 'pengakuan') !== false || mb_strpos($lower, 'butiran') !== false) {
            return 4;
        }
        if (mb_strpos($lower, 'tanggungan') !== false || mb_strpos($lower, 'tempat tinggal') !== false
            || mb_strpos($lower, 'harta') !== false || mb_strpos($lower, 'pekerjaan') !== false
            || mb_strpos($lower, 'pendapatan') !== false) {
            return 3;
        }
        if (mb_strpos($lower, 'tambahan') !== false) {
            return 2;
        }

        return 1;
    }
}
