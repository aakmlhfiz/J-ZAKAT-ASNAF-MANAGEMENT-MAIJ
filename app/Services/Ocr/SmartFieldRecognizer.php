<?php

namespace App\Services\Ocr;

use App\Services\Ai\AiService;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\MockProvider;
use App\Services\Ai\Providers\OllamaProvider;
use App\Services\Ai\Providers\OpenAiProvider;

class SmartFieldRecognizer
{
    private AiService $ai;

    private string $recognizer;

    private int $confidenceThreshold;

    public function __construct()
    {
        $this->ai = new AiService;
        $this->recognizer = config('ocr.ai_recognizer', 'mock');
        $this->confidenceThreshold = (int) config('ocr.confidence_threshold', 70);
    }

    public function enhance(array $regexResult, string $rawText): array
    {
        if (empty($this->recognizer)) {
            return $regexResult;
        }

        try {
            $provider = $this->getProviderInstance();

            if ($provider instanceof MockProvider) {
                return $this->mockEnhance($regexResult, $rawText);
            }

            $prompt = $this->buildPrompt($rawText, $regexResult);
            $response = $provider->chat($prompt, []);

            $aiResult = $this->parseAiResponse($response['reply'] ?? '', $regexResult);

            return $aiResult ?? $regexResult;

        } catch (\Throwable $e) {
            report($e);

            return $regexResult;
        }
    }

    private function mockEnhance(array $regexResult, string $rawText): array
    {
        $fields = $regexResult['fields'] ?? [];
        $enhanced = [];

        foreach ($fields as $key => $data) {
            $value = $data['value'] ?? null;
            $confidence = $data['confidence'] ?? 0;

            if ($value !== null) {
                $improved = $this->applyMockRules($key, $value, $confidence, $rawText);
                $enhanced[$key] = array_merge($data, $improved);
            } else {
                $fallback = $this->fallbackExtract($key, $rawText);
                if ($fallback['value'] !== null) {
                    $enhanced[$key] = array_merge($data, $fallback);
                } else {
                    $enhanced[$key] = $data;
                }
            }
        }

        $householdMembersRegex = $regexResult['household_members'] ?? [];
        $betterMembers = $this->mockEnhanceHouseholdMembers($householdMembersRegex, $rawText);

        $confidences = array_column($enhanced, 'confidence');
        $overallConfidence = count($confidences) > 0
            ? round(array_sum($confidences) / count($confidences), 2)
            : ($regexResult['overall_confidence'] ?? 0);

        return [
            'fields' => $enhanced,
            'overall_confidence' => $overallConfidence,
            'matched_labels' => $regexResult['matched_labels'] ?? [],
            'household_members' => $betterMembers,
            'sections_detected' => $regexResult['sections_detected'] ?? [],
        ];
    }

    private function applyMockRules(string $field, string $value, int $confidence, string $rawText): array
    {
        $cleaned = $this->cleanValue($field, $value);

        if ($cleaned === null && $confidence > 0) {
            return ['value' => $value, 'confidence' => max(15, $confidence - 40), 'needs_review' => true];
        }

        if ($cleaned !== $value) {
            $confidence = min(100, $confidence + 5);
        }

        $needsReview = $confidence < $this->confidenceThreshold;

        return [
            'value' => $cleaned ?? $value,
            'confidence' => $confidence,
            'needs_review' => $needsReview,
        ];
    }

    private function cleanValue(string $field, string $value): ?string
    {
        $value = trim($value);

        switch ($field) {
            case 'ic':
            case 'ic_ketua_rumah':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) >= 10) {
                    return substr($digits, 0, 12);
                }
                if (preg_match('/(\d{6})-?(\d{2})-?(\d{4})/', $value, $m)) {
                    return $m[1].$m[2].$m[3];
                }
                break;

            case 'telefon':
            case 'no_telefon_bimbit':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) >= 9 && strlen($digits) <= 12) {
                    if (preg_match('/^(\d{2,3})(\d{3,4})(\d{3,4})$/', $digits, $m)) {
                        return $m[1].'-'.$m[2].' '.$m[3];
                    }

                    return $digits;
                }
                break;

            case 'no_telefon_rumah':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) >= 6 && strlen($digits) <= 12) {
                    return $digits;
                }
                break;

            case 'pendapatan':
            case 'pendapatan_pasangan':
            case 'pendapatan_isi_rumah':
            case 'pendapatan_lain':
            case 'sewa':
            case 'utiliti':
            case 'pengangkutan':
            case 'perubatan':
            case 'perbelanjaan':
            case 'jumlah_bantuan_diperlukan':
                $cleaned = preg_replace('/[^0-9.]/', '', $value);
                if (is_numeric($cleaned) && (float) $cleaned > 0) {
                    return number_format((float) $cleaned, 2, '.', '');
                }
                break;

            case 'poskod':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) >= 5) {
                    return substr($digits, 0, 5);
                }
                break;

            case 'status_perkahwinan':
                $lower = mb_strtolower($value);
                $map = [
                    'bujang' => 'Bujang', 'single' => 'Bujang',
                    'berkahwin' => 'Berkahwin', 'kahwin' => 'Berkahwin', 'married' => 'Berkahwin',
                    'duda' => 'Duda', 'janda' => 'Janda',
                    'divorced' => 'Bercerai', 'widowed' => 'Balu',
                ];
                foreach ($map as $key => $val) {
                    if (mb_strpos($lower, $key) !== false) {
                        return $val;
                    }
                }
                break;

            case 'merokok':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['ya', 'yes', 'y'])) {
                    return 'Ya';
                }
                if (in_array($lower, ['tidak', 'no', 'n', 't'])) {
                    return 'Tidak';
                }
                break;

            case 'perumahan':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['sendiri', 'own', 'owned'])) {
                    return 'Sendiri';
                }
                if (in_array($lower, ['sewa', 'rent', 'rental'])) {
                    return 'Sewa';
                }
                break;
        }

        return $value;
    }

    private function fallbackExtract(string $field, string $rawText): array
    {
        $lines = explode("\n", $rawText);

        if ($field === 'alamat') {
            $addressLines = [];
            $capture = false;
            foreach ($lines as $line) {
                $lower = mb_strtolower($line);
                if (mb_strpos($lower, 'alamat') !== false || mb_strpos($lower, 'address') !== false) {
                    $capture = true;

                    continue;
                }
                if ($capture) {
                    if ($this->isLikelyLabel($line)) {
                        break;
                    }
                    $trimmed = trim($line);
                    if (! empty($trimmed)) {
                        $addressLines[] = $trimmed;
                    }
                    if (count($addressLines) >= 3) {
                        break;
                    }
                }
            }
            if (! empty($addressLines)) {
                return ['value' => implode(', ', $addressLines), 'confidence' => 65, 'needs_review' => true];
            }
        }

        if ($field === 'poskod') {
            foreach ($lines as $line) {
                if (preg_match('/\b(\d{5})\b/', $line, $m)) {
                    return ['value' => $m[1], 'confidence' => 70, 'needs_review' => false];
                }
            }
        }

        return ['value' => null, 'confidence' => 0, 'needs_review' => false];
    }

    private function isLikelyLabel(string $line): bool
    {
        foreach (FieldSchema::$labels as $variants) {
            foreach ($variants as $label) {
                if (mb_strlen($label) > 3 && mb_stripos($line, $label) !== false) {
                    return true;
                }
            }
        }
        foreach (FieldSchema::sectionLabels() as $label => $cat) {
            if (mb_strlen($label) > 3 && mb_stripos($line, $label) !== false) {
                return true;
            }
        }

        return false;
    }

    private function mockEnhanceHouseholdMembers(array $members, string $rawText): array
    {
        if (! empty($members)) {
            return array_map(function ($member) {
                if (isset($member['ic'])) {
                    $member['ic'] = preg_replace('/\D/', '', $member['ic']);
                    $member['ic'] = substr($member['ic'], 0, 12);
                }
                if (isset($member['umur']) && is_string($member['umur'])) {
                    $member['umur'] = (int) $member['umur'];
                }

                return $member;
            }, $members);
        }

        return [];
    }

    private function buildPrompt(string $rawText, array $regexResult): string
    {
        $schema = FieldSchema::toPromptText();
        $fields = [];

        foreach ($regexResult['fields'] ?? [] as $key => $data) {
            $v = $data['value'] ?? '(not found)';
            $fields[] = "- {$key}: {$v}";
        }

        $fieldsText = implode("\n", $fields);

        return <<<PROMPT
You are a document data extraction AI for the Malaysian MAIJ Asnaf Zakat system.

Extract the following fields from this OCR text of a MAIJ Zakat Assistance Application Form (Borang Permohonan Bantuan MAIJ).
Return ONLY a JSON object.

The form contains these sections:
- MAKLUMAT PEMOHON (Applicant Information)
- MAKLUMAT ISI RUMAH (Household Members) - this is a TABLE with multiple rows
- MAKLUMAT KEWANGAN (Financial Information)
- JENIS BANTUAN (Assistance Types)

AVAILABLE FIELDS (first label is the primary, hints in brackets):
{$schema}

OCR TEXT:
---
{$rawText}
---

REGEX-PRE-EXTRACTED PREVIEW:
{$fieldsText}

INSTRUCTIONS:
1. For each field, extract the most likely value from the OCR text.
2. Include a confidence score (0-100) for each extraction.
3. If a field is not found, set value to null and confidence to 0.
4. For IC numbers, extract exactly 12 digits.
5. For phone numbers, normalize format (e.g., 012-345 6789).
6. For monetary values, extract as numbers without 'RM' prefix.
7. For checkbox/multiple-choice fields, return the selected option text.
8. For household members TABLE section, extract ALL rows as an array under "household_members" key.
9. Each household member row should have: nama, ic, hubungan, umur, pekerjaan, pendapatan_bulanan, pendidikan, status_kesihatan.
10. Respond with ONLY valid JSON, no markdown, no explanation.

FORMAT:
{
  "fields": {
    "nama": {"value": "Ali Bin Abu", "confidence": 95},
    "ic": {"value": "800315015678", "confidence": 99},
    ...
  },
  "overall_confidence": 87.5,
  "household_members": [
    {"nama": "Siti", "ic": "800315015679", "hubungan": "Isteri", "umur": 42, "pekerjaan": "Suri Rumah", "pendapatan_bulanan": 0, "pendidikan": "SPM", "status_kesihatan": "Sihat"},
    ...
  ]
}
PROMPT;
    }

    private function parseAiResponse(string $reply, array $regexResult): ?array
    {
        $json = $this->extractJson($reply);

        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);

        if (! is_array($data) || ! isset($data['fields'])) {
            return null;
        }

        foreach ($data['fields'] as $field => &$entry) {
            if (! is_array($entry)) {
                $entry = [
                    'value' => $entry,
                    'confidence' => 50,
                    'source_label' => null,
                    'needs_review' => true,
                ];
            } else {
                $entry['source_label'] = $entry['source_label'] ?? null;
                $entry['confidence'] = (int) ($entry['confidence'] ?? 50);
                $entry['needs_review'] = ($entry['confidence'] ?? 0) < $this->confidenceThreshold;
            }
        }

        $data['overall_confidence'] = round((float) ($data['overall_confidence'] ?? 50), 2);
        $data['matched_labels'] = $data['matched_labels'] ?? $regexResult['matched_labels'] ?? [];
        $data['household_members'] = $data['household_members'] ?? $regexResult['household_members'] ?? [];
        $data['sections_detected'] = $data['sections_detected'] ?? $regexResult['sections_detected'] ?? [];

        return $data;
    }

    private function extractJson(string $text): ?string
    {
        $text = trim($text);

        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            return $m[0];
        }

        return null;
    }

    private function getProviderInstance()
    {
        return match ($this->recognizer) {
            'mock' => new MockProvider,
            'openai' => new OpenAiProvider,
            'gemini' => new GeminiProvider,
            'ollama' => new OllamaProvider,
            default => new MockProvider,
        };
    }
}
