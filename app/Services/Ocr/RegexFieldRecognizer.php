<?php

namespace App\Services\Ocr;

class RegexFieldRecognizer
{
    private int $confidenceThreshold;

    public function __construct()
    {
        $this->confidenceThreshold = (int) config('ocr.confidence_threshold', 70);
    }

    public function recognize(string $text): array
    {
        $sections = $this->detectSections($text);
        $result = [];

        $allCategories = FieldSchema::fieldCategories();

        foreach (FieldSchema::fields() as $field) {
            $category = $this->fieldCategory($field);
            $sectionText = $sections[$category] ?? $text;
            $result[$field] = $this->extractField($field, $sectionText);
        }

        $householdMembers = $this->extractHouseholdMembers($text);
        $confidences = array_column($result, 'confidence');
        $overallConfidence = count($confidences) > 0
            ? round(array_sum($confidences) / count($confidences), 2)
            : 0;

        return [
            'fields' => $result,
            'overall_confidence' => $overallConfidence,
            'matched_labels' => $this->collectMatchedLabels($result),
            'household_members' => $householdMembers,
            'sections_detected' => array_keys(array_filter($sections, fn ($s) => $s !== $text)),
        ];
    }

    private function detectSections(string $text): array
    {
        $sections = [];
        $lines = explode("\n", $text);
        $currentSection = null;
        $currentLines = [];

        foreach ($lines as $line) {
            $detected = $this->detectSectionLabel($line);
            if ($detected !== null) {
                if ($currentSection !== null && ! empty($currentLines)) {
                    $sections[$currentSection] = implode("\n", $currentLines);
                }
                $currentSection = $detected;
                $currentLines = [];

                continue;
            }
            $currentLines[] = $line;
        }

        if ($currentSection !== null && ! empty($currentLines)) {
            $sections[$currentSection] = implode("\n", $currentLines);
        }

        if (empty($sections)) {
            $sections['_all'] = $text;
        }

        return $sections;
    }

    private function detectSectionLabel(string $line): ?string
    {
        $normalized = mb_strtolower(trim($line));

        foreach (FieldSchema::sectionLabels() as $label => $category) {
            if (mb_strpos($normalized, mb_strtolower($label)) !== false) {
                return $category;
            }
        }

        return null;
    }

    private function fieldCategory(string $field): string
    {
        foreach (FieldSchema::fieldCategories() as $category => $fields) {
            if (in_array($field, $fields, true)) {
                return $category;
            }
        }

        return '_all';
    }

    private function extractField(string $field, string $text): array
    {
        $value = null;
        $confidence = 0;
        $sourceLabel = null;

        foreach (FieldSchema::labelsFor($field) as $label) {
            $extracted = $this->extractValueForLabel($label, $text);

            if ($extracted['value'] !== null && $extracted['confidence'] > $confidence) {
                $value = $extracted['value'];
                $confidence = $extracted['confidence'];
                $sourceLabel = $label;
            }
        }

        if ($value !== null) {
            $confidence = $this->applyFormatValidation($field, $value, $confidence);
        }

        return [
            'value' => $value,
            'confidence' => $confidence,
            'source_label' => $sourceLabel,
            'needs_review' => $confidence < $this->confidenceThreshold,
        ];
    }

    private function extractValueForLabel(string $label, string $text): array
    {
        $labelPattern = preg_quote($label, '/');

        $patterns = [
            "/{$labelPattern}\s*:?\s*(.+?)(?:\n|$)/iu",
            "/{$labelPattern}\s*:?\s*(.+)/iu",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $value = trim($m[1]);

                if ($value !== '' && mb_strlen($value) > 1) {
                    $labelConfidence = $this->labelSpecificConfidence($label);

                    return [
                        'value' => $value,
                        'confidence' => $labelConfidence,
                    ];
                }
            }
        }

        $search = mb_strtolower($label);
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            $lowerLine = mb_strtolower($line);

            if (mb_strpos($lowerLine, $search) !== false) {
                $after = trim(mb_substr($line, mb_strlen($label)));
                $after = ltrim($after, ': \t');

                if ($after !== '' && mb_strlen($after) > 1) {
                    if ($this->isLikelyLabel($after)) {
                        $after = null;
                    }
                } else {
                    $after = null;
                }

                if ($after === null && isset($lines[$i + 1])) {
                    $nextLine = $lines[$i + 1];
                    if (isset($lines[$i + 2])) {
                        $thirdLine = $lines[$i + 2];
                        if (! $this->isLikelyLabel($nextLine) && ! $this->isLikelyLabel($thirdLine)) {
                            $after = trim($nextLine."\n".$thirdLine);
                        }
                    }

                    if ($after === null) {
                        $after = trim($nextLine);
                    }

                    if ($this->isLikelyLabel($after)) {
                        $after = null;
                    }
                }

                if ($after !== null) {
                    return [
                        'value' => $after,
                        'confidence' => 55,
                    ];
                }
            }
        }

        $fuzzy = $this->fuzzyExtractForLabel($label, $text);
        if ($fuzzy !== null) {
            return $fuzzy;
        }

        return ['value' => null, 'confidence' => 0];
    }

    /**
     * Fallback tolerant of OCR typos on field labels: slide a window of the
     * label across each line and accept a match when the Levenshtein distance
     * is small (e.g. "No. Kad Pengenalan" → "No Kad Pengenalan").
     */
    private function fuzzyExtractForLabel(string $label, string $text): ?array
    {
        $labelLower = mb_strtolower(trim($label));
        if (mb_strlen($labelLower) < 5) {
            return null;
        }

        $maxDistance = max(1, (int) floor(mb_strlen($labelLower) / 6));
        $labelLen = mb_strlen($labelLower);
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            $lineLower = mb_strtolower($line);
            $lineLen = mb_strlen($lineLower);

            for ($pos = 0; $pos + $labelLen <= $lineLen; $pos++) {
                $window = mb_substr($lineLower, $pos, $labelLen);

                if (levenshtein($window, $labelLower) > $maxDistance) {
                    continue;
                }

                $after = $this->lineValueAfter(mb_substr($line, $pos + $labelLen), $lines, $i);

                if ($after !== null) {
                    return ['value' => $after, 'confidence' => 62];
                }
            }
        }

        return null;
    }

    private function lineValueAfter(string $remainder, array $lines, int $lineIndex): ?string
    {
        $after = trim($remainder);
        $after = ltrim($after, ': \t-–');

        if ($after !== '' && mb_strlen($after) > 1 && ! $this->isLikelyLabel($after)) {
            return $after;
        }

        if (isset($lines[$lineIndex + 1])) {
            $nextLine = trim($lines[$lineIndex + 1]);
            if ($nextLine !== '' && ! $this->isLikelyLabel($nextLine)) {
                return $nextLine;
            }
        }

        return null;
    }

    private function labelSpecificConfidence(string $label): int
    {
        $lower = mb_strtolower($label);

        if (mb_strpos($lower, 'nama') !== false && (mb_strpos($lower, 'penuh') !== false || mb_strpos($lower, 'pemohon') !== false)) {
            return 92;
        }
        if (mb_strpos($lower, 'no ic') !== false || mb_strpos($lower, 'no kad') !== false || mb_strpos($lower, 'nric') !== false) {
            return 95;
        }
        if (mb_strpos($lower, 'no telefon') !== false || mb_strpos($lower, 'mobile') !== false) {
            return 85;
        }
        if (mb_strpos($lower, 'alamat') !== false) {
            return 88;
        }
        if (mb_strpos($lower, 'pendapatan') !== false || mb_strpos($lower, 'gaji') !== false) {
            return 82;
        }

        return 70;
    }

    private function isLikelyLabel(string $str): bool
    {
        $trimmed = trim($str);
        if ($trimmed === '' || $trimmed === '-' || $trimmed === '—') {
            return true;
        }

        foreach (FieldSchema::$labels as $variants) {
            foreach ($variants as $label) {
                if (mb_stripos($trimmed, $label) !== false && mb_strlen($label) > 3) {
                    return true;
                }
            }
        }

        foreach (FieldSchema::sectionLabels() as $sectionLabel => $cat) {
            if (mb_stripos($trimmed, $sectionLabel) !== false) {
                return true;
            }
        }

        return false;
    }

    private function extractHouseholdMembers(string $text): array
    {
        $members = [];
        $sectionCategory = null;

        foreach (FieldSchema::sectionLabels() as $label => $category) {
            if ($category === 'household_members' && mb_stripos($text, $label) !== false) {
                $sectionCategory = $category;
                break;
            }
        }

        if ($sectionCategory === null && empty($this->detectTablePattern($text))) {
            return $members;
        }

        $tableRows = $this->detectTablePattern($text);

        if (empty($tableRows)) {
            $tableRows = $this->detectRepeatedMemberPattern($text);
        }

        foreach ($tableRows as $row) {
            $memberData = $this->parseMemberRow($row);
            if (! empty($memberData['nama']) || ! empty($memberData['ic'])) {
                $members[] = $memberData;
            }
        }

        return $members;
    }

    private function detectTablePattern(string $text): array
    {
        $lines = explode("\n", $text);
        $rows = [];
        $inTable = false;
        $headerTerms = ['nama', 'ic', 'hubungan', 'umur', 'pekerjaan'];

        foreach ($lines as $line) {
            $lower = mb_strtolower($line);

            $headerMatches = 0;
            foreach ($headerTerms as $term) {
                if (mb_strpos($lower, $term) !== false) {
                    $headerMatches++;
                }
            }

            if ($headerMatches >= 2) {
                $inTable = true;

                continue;
            }

            if ($inTable) {
                // A row that contains a valid Malaysian IC is member data, not
                // a section header — even if it happens to include a common
                // word that also appears in a field label (e.g. "Rumah").
                $looksLikeMemberRow = (bool) preg_match('/\d{6}[\s\-]?\d{2}[\s\-]?\d{4}/', $line);

                if (! $looksLikeMemberRow && ($this->isLikelyLabel($line) || $this->detectSectionLabel($line) !== null)) {
                    $inTable = false;

                    continue;
                }

                $cleaned = preg_replace('/\s+/', ' ', trim($line));
                if (! empty($cleaned) && mb_strlen($cleaned) > 5) {
                    $rows[] = $cleaned;
                }
            }
        }

        return $rows;
    }

    private function detectRepeatedMemberPattern(string $text): array
    {
        $rows = [];
        $lines = explode("\n", $text);
        $memberPrefixes = ['Nama:', 'IC:', 'Umur:', 'Pekerjaan:', 'Pendapatan:', 'Pendidikan:', 'Kesihatan:', 'Name:', 'Age:', 'Occupation:', 'Income:', 'Education:', 'Health:'];

        $currentMember = [];
        $fieldLabels = FieldSchema::tabularFields();

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $isMemberField = false;

            foreach ($memberPrefixes as $prefix) {
                if (mb_stripos($trimmed, $prefix) === 0) {
                    $currentMember[] = $trimmed;
                    $isMemberField = true;
                    break;
                }
            }

            if (! $isMemberField && ! empty($currentMember)) {
                if (count($currentMember) >= 2) {
                    $rows[] = implode(' | ', $currentMember);
                }
                $currentMember = [];
            }
        }

        if (! empty($currentMember) && count($currentMember) >= 2) {
            $rows[] = implode(' | ', $currentMember);
        }

        return $rows;
    }

    private function parseMemberRow(string $row): array
    {
        $data = [
            'nama' => null,
            'ic' => null,
            'hubungan' => null,
            'umur' => null,
            'pekerjaan' => null,
            'pendapatan_bulanan' => null,
            'pendidikan' => null,
            'status_kesihatan' => null,
        ];

        $tokens = array_values(array_filter(explode(' ', $row), fn ($t) => ! empty(trim($t))));

        if (empty($tokens)) {
            return $data;
        }

        $icPattern = '/\d{6}[\-\s]?\d{2}[\-\s]?\d{4}/';
        $icToken = null;
        $icIndex = -1;
        foreach ($tokens as $idx => $token) {
            if (preg_match($icPattern, $token)) {
                $icToken = $token;
                $icIndex = $idx;
                break;
            }
        }

        if ($icToken) {
            $data['ic'] = preg_replace('/\D/', '', $icToken);

            if ($icIndex > 0) {
                $data['nama'] = implode(' ', array_slice($tokens, 0, $icIndex));
            }

            $remaining = array_slice($tokens, $icIndex + 1);
            $relationships = ['Suami', 'Isteri', 'Anak', 'Ibu', 'Bapa', 'Menantu', 'Cucu', 'Abang', 'Kakak', 'Adik', 'Husband', 'Wife', 'Child', 'Mother', 'Father', 'Son', 'Daughter'];

            foreach ($remaining as $token) {
                foreach ($relationships as $rel) {
                    if (mb_stripos($token, $rel) !== false) {
                        if (! $data['hubungan']) {
                            $data['hubungan'] = $rel;
                        }
                        break 2;
                    }
                }
            }

            foreach ($remaining as $token) {
                if (is_numeric($token) && (int) $token >= 1 && (int) $token <= 120) {
                    $data['umur'] = (int) $token;
                    break;
                }
            }
        } elseif (count($tokens) >= 1) {
            $data['nama'] = $tokens[0];
            if (count($tokens) >= 2) {
                $data['nama'] .= ' '.$tokens[1];
            }
        }

        return $data;
    }

    private function applyFormatValidation(string $field, string $value, int $confidence): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-' || $value === '—') {
            return 0;
        }

        switch ($field) {
            case 'ic':
            case 'ic_ketua_rumah':
                $digits = preg_replace('/\D/', '', $value);
                if (preg_match('/^\d{12}$/', $digits)) {
                    return min(100, $confidence + 25);
                }
                if (mb_strpos($value, $digits) !== false && strlen($digits) === 12) {
                    return $confidence + 10;
                }
                if (strlen($digits) >= 10 && strlen($digits) <= 14) {
                    return max(30, $confidence - 10);
                }

                return max(20, $confidence - 30);

            case 'telefon':
            case 'no_telefon_bimbit':
            case 'no_telefon_rumah':
                $digits = preg_replace('/\D/', '', $value);
                if (preg_match('/^0/', $digits) && strlen($digits) >= 9 && strlen($digits) <= 12) {
                    return min(100, $confidence + 25);
                }
                if (strlen($digits) >= 7 && strlen($digits) <= 13) {
                    return max(50, $confidence);
                }

                return max(20, $confidence - 20);

            case 'pendapatan':
            case 'pendapatan_pasangan':
            case 'pendapatan_isi_rumah':
            case 'sewa':
            case 'utiliti':
            case 'pengangkutan':
            case 'perubatan':
            case 'perbelanjaan':
            case 'jumlah_bantuan_diperlukan':
                $numeric = preg_replace('/[^\d.]/', '', $value);
                if (is_numeric($numeric) && (float) $numeric >= 0) {
                    return min(100, $confidence + 15);
                }

                return max(15, $confidence - 40);

            case 'bilangan_tanggungan':
                $numeric = preg_replace('/[^\d]/', '', $value);
                if (is_numeric($numeric) && (int) $numeric >= 0 && (int) $numeric <= 30) {
                    return min(100, $confidence + 20);
                }

                return max(15, $confidence - 30);

            case 'poskod':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) === 5) {
                    return min(100, $confidence + 20);
                }

                return max(20, $confidence - 20);

            case 'perumahan':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['sendiri', 'sewa', 'own', 'rent', 'rental', 'owned'])) {
                    return min(100, $confidence + 20);
                }

                return max(40, $confidence - 10);

            case 'status_perkahwinan':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['bujang', 'berkahwin', 'duda', 'janda', 'single', 'married', 'divorced', 'widowed', 'kahwin'])) {
                    return min(100, $confidence + 20);
                }

                return max(40, $confidence - 10);

            case 'merokok':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['ya', 'tidak', 'yes', 'no', 'y', 'n', 't'])) {
                    return min(100, $confidence + 20);
                }

                return max(30, $confidence - 10);

            case 'bantuan_sara_hidup':
            case 'bantuan_perubatan':
            case 'bantuan_berdikari':
            case 'bantuan_perumahan':
            case 'menjelaskan_hutang':
                $lower = mb_strtolower($value);
                if (in_array($lower, ['ya', 'tidak', 'yes', 'no', 'y', 'n', 't', '✓', '✔', '☑', 'x', '✗', '✘'])) {
                    return min(100, $confidence + 20);
                }
                if (is_numeric($value)) {
                    return min(100, $confidence + 15);
                }

                return max(30, $confidence - 15);

            default:
                return $confidence;
        }
    }

    private function collectMatchedLabels(array $result): array
    {
        $labels = [];

        foreach ($result as $field => $data) {
            if (($data['source_label'] ?? null) !== null) {
                $labels[] = [
                    'field' => $field,
                    'label' => $data['source_label'],
                    'confidence' => $data['confidence'],
                ];
            }
        }

        return $labels;
    }
}
