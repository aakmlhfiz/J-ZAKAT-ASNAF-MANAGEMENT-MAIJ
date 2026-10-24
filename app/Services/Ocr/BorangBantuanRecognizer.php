<?php

namespace App\Services\Ocr;

/**
 * Dedicated recognizer for the MAIJ "Borang Agihan Zakat" (Borang Bantuan MAIJ).
 *
 * Responsibilities:
 *  - Detect checkboxes (☑ / ☐ / ✓ / ✗ / [x] / [ ]) and their associated options.
 *  - Parse tabulated sections (Bantuan types, Harta, Pendapatan, Perbelanjaan).
 *  - Hand off to the existing Regex + Smart (LLM) recognizers for the rest.
 *  - Merge Borang-specific fields into the canonical {fields, household_members, ...} output.
 */
class BorangBantuanRecognizer
{
    private RegexFieldRecognizer $regexRecognizer;

    private SmartFieldRecognizer $smartRecognizer;

    private int $confidenceThreshold;

    public function __construct()
    {
        $this->regexRecognizer = new RegexFieldRecognizer;
        $this->smartRecognizer = new SmartFieldRecognizer;
        $this->confidenceThreshold = (int) config('ocr.confidence_threshold', 70);
    }

    /**
     * Run recognition for a Borang Bantuan MAIJ document.
     *
     * @return array{fields: array, household_members: array, overall_confidence: float, matched_labels: array, sections_detected: array, borang: array}
     */
    public function recognize(string $rawText): array
    {
        $regexResult = $this->regexRecognizer->recognize($rawText);
        $enhanced = $this->smartRecognizer->enhance($regexResult, $rawText);

        return $this->recognizeFrom($rawText, $regexResult, $enhanced);
    }

    /**
     * Recognize a Borang Bantuan MAIJ document reusing pre-computed regex/AI
     * results. Used by OcrService so recognition only runs once per import
     * (avoiding duplicated regex + LLM calls).
     */
    public function recognizeFrom(string $rawText, array $regexResult, array $enhancedResult): array
    {
        $regexResult = $regexResult ?: $this->regexRecognizer->recognize($rawText);
        $enhancedResult = $enhancedResult ?: $this->smartRecognizer->enhance($regexResult, $rawText);

        $fields = $enhancedResult['fields'] ?? $regexResult['fields'] ?? [];

        $borangFields = $this->recognizeBorangFields($rawText, $fields);
        $fields = $this->mergeFields($fields, $borangFields);

        $householdMembers = $enhancedResult['household_members'] ?? $regexResult['household_members'] ?? [];
        $householdMembers = $this->recognizeTanggunganTable($rawText, $householdMembers);

        $pendapatanPerbelanjaan = $this->recognizeFinancialTables($rawText);

        $checkboxes = $this->detectCheckboxes($rawText);

        $confidenceValues = array_filter(array_map(
            fn ($f) => ($f['value'] ?? null) !== null ? (float) ($f['confidence'] ?? 0) : null,
            $fields
        ));
        $overallConfidence = count($confidenceValues) > 0
            ? round(array_sum($confidenceValues) / count($confidenceValues), 2)
            : 0;

        return [
            'fields' => $fields,
            'household_members' => $householdMembers,
            'overall_confidence' => $overallConfidence,
            'matched_labels' => $enhancedResult['matched_labels'] ?? $regexResult['matched_labels'] ?? [],
            'sections_detected' => $enhancedResult['sections_detected'] ?? $regexResult['sections_detected'] ?? [],
            'borang' => [
                'checkboxes' => $checkboxes,
                'pendapatan' => $pendapatanPerbelanjaan['pendapatan'] ?? [],
                'perbelanjaan' => $pendapatanPerbelanjaan['perbelanjaan'] ?? [],
                'harta' => $pendapatanPerbelanjaan['harta'] ?? [],
                'pekerjaan' => $pendapatanPerbelanjaan['pekerjaan'] ?? [],
            ],
        ];
    }

    /**
     * Process Borang-specific field sections (Sara Hidup, Perubatan, Berdikari,
     * Perumahan, Hutang, Tempat Tinggal, Harta, Pekerjaan, Pendapatan, Perbelanjaan).
     */
    private function recognizeBorangFields(string $text, array $existingFields): array
    {
        $fields = [];

        $sectionMap = [
            'sara_hidup' => 'A. SARA HIDUP',
            'perubatan' => 'B. PERUBATAN',
            'berdikari' => 'C. BERDIKARI',
            'perumahan' => 'D. PERUMAHAN',
            'hutang' => 'E. MENJELASKAN HUTANG',
            'tempat_tinggal' => 'MAKLUMAT TEMPAT TINGGAL',
            'harta' => 'MAKLUMAT HARTA',
            'pekerjaan' => 'MAKLUMAT PEKERJAAN PEMOHON/PASANGAN',
            'pendapatan' => 'MAKLUMAT PENDAPATAN',
            'perbelanjaan' => 'PERBELANJAAN BULANAN',
        ];

        $sections = $this->splitSections($text, $sectionMap);

        if (isset($sections['sara_hidup'])) {
            $fields['jumlah_sara_hidup'] = $this->makeField(
                $this->extractAmount($sections['sara_hidup']),
                70,
                'JUM. Yang Diperlukan'
            );
            $fields['no_akaun_bimb'] = $this->makeField(
                $this->extractLabeledValue($sections['sara_hidup'], 'No. Akaun BIMB'),
                70,
                'No. Akaun BIMB'
            );
            $fields['bantuan_agensi_lain_ada'] = $this->makeField(
                $this->hasCheckboxChecked($sections['sara_hidup'], 'Ada') ? 'Ya' : null,
                70,
                'Bantuan daripada Agensi lain'
            );
            $fields['tidak_pernah_terima_bantuan'] = $this->makeField(
                $this->hasCheckboxChecked($sections['sara_hidup'], 'Tidak pernah') ? 'Ya' : null,
                65,
                'Tidak pernah menerima'
            );
        }

        if (isset($sections['perubatan'])) {
            $fields['perubatan_jenis_rawatan'] = $this->makeField(
                $this->detectCheckedOption($sections['perubatan'], ['Dialisis', 'Pembedahan']),
                70,
                'Jenis Rawatan'
            );
            $fields['perubatan_jenis_penyakit'] = $this->makeField(
                $this->extractLabeledValue($sections['perubatan'], 'Jenis Penyakit'),
                70,
                'Jenis Penyakit'
            );
            $fields['perubatan_tempat_rawatan'] = $this->makeField(
                $this->extractLabeledValue($sections['perubatan'], 'Tempat Rawatan'),
                70,
                'Tempat Rawatan'
            );
            $fields['perubatan_kos_rawatan'] = $this->makeField(
                $this->extractAmount($sections['perubatan']),
                70,
                'Kos Rawatan'
            );
            $fields['perubatan_harga_alatan'] = $this->makeField(
                $this->extractLabeledValue($sections['perubatan'], 'Harga Alatan'),
                65,
                'Harga Alatan'
            );
        }

        if (isset($sections['berdikari'])) {
            $fields['jenis_berdikari'] = $this->makeField(
                $this->detectCheckedOption($sections['berdikari'], ['Peralatan', 'Modal']),
                70,
                'Berdikari'
            );
            $fields['berdikari_jenis_peralatan'] = $this->makeField(
                $this->extractLabeledValue($sections['berdikari'], 'Jenis peralatan yang dipohon'),
                70,
                'Jenis peralatan yang dipohon'
            );
            $fields['berdikari_harga_peralatan'] = $this->makeField(
                $this->extractLabeledValue($sections['berdikari'], 'Harga'),
                70,
                'Harga Peralatan'
            );
            $fields['berdikari_jenis_perniagaan'] = $this->makeField(
                $this->extractLabeledValue($sections['berdikari'], 'Jenis Perniagaan'),
                70,
                'Jenis Perniagaan'
            );
        }

        if (isset($sections['perumahan'])) {
            $fields['jenis_bantuan_perumahan'] = $this->makeField(
                $this->detectCheckedOption($sections['perumahan'], ['Bina Baru', 'Baik Pulih']),
                70,
                'Bantuan Perumahan'
            );
            $fields['status_tanah'] = $this->makeField(
                $this->detectCheckedOption($sections['perumahan'], ['Sendiri', 'Sewa', 'Tumpang']),
                70,
                'Status Tanah'
            );
            $fields['nama_dalam_geran'] = $this->makeField(
                $this->extractLabeledValue($sections['perumahan'], 'Nama dalam Geran'),
                70,
                'Nama dalam Geran'
            );
            $fields['no_geran'] = $this->makeField(
                $this->extractLabeledValue($sections['perumahan'], 'No. Geran'),
                70,
                'No. Geran'
            );
        }

        if (isset($sections['hutang'])) {
            $fields['sebab_berhutang'] = $this->makeField(
                $this->extractLabeledValue($sections['hutang'], 'Sebab berhutang'),
                70,
                'Sebab berhutang'
            );
            $fields['jumlah_hutang'] = $this->makeField(
                $this->extractLabeledValue($sections['hutang'], 'Jumlah Hutang'),
                70,
                'Jumlah Hutang'
            );
            $fields['sebab_gagal_bayar_hutang'] = $this->makeField(
                $this->extractLabeledValue($sections['hutang'], 'Sebab gagal bayar'),
                70,
                'Sebab gagal bayar'
            );
            $fields['nama_pemberi_hutang'] = $this->makeField(
                $this->extractLabeledValue($sections['hutang'], 'Nama Pemberi Hutang'),
                70,
                'Nama Pemberi Hutang'
            );
            $fields['no_kp_pemberi_hutang'] = $this->makeField(
                $this->extractLabeledValue($sections['hutang'], 'No. K/P Pemberi Hutang'),
                70,
                'No. K/P Pemberi Hutang'
            );
        }

        if (isset($sections['tempat_tinggal'])) {
            $fields['tempat_tinggal_sendiri'] = $this->makeField(
                $this->hasCheckboxChecked($sections['tempat_tinggal'], 'Sendiri') ? 'Ya' : null,
                70,
                'Sendiri'
            );
            $fields['tempat_tinggal_menumpang'] = $this->makeField(
                $this->hasCheckboxChecked($sections['tempat_tinggal'], 'Menumpang') ? 'Ya' : null,
                70,
                'Menumpang'
            );
            $fields['tempat_tinggal_menyewe'] = $this->makeField(
                $this->hasCheckboxChecked($sections['tempat_tinggal'], 'Menyewe') ? 'Ya' : null,
                70,
                'Menyewe'
            );
            $fields['lain_lain_nyatakan_tempat_tinggal'] = $this->makeField(
                $this->extractLabeledValue($sections['tempat_tinggal'], 'Lain-lain'),
                65,
                'Lain-lain Nyatakan'
            );
        }

        if (isset($sections['harta'])) {
            $fields['harta_rumah'] = $this->makeField(
                $this->hasCheckboxChecked($sections['harta'], 'Rumah') ? 'Ya' : null,
                $this->hasCheckboxChecked($sections['harta'], 'Rumah') ? 85 : 70,
                'Rumah'
            );
            $fields['harta_tanah'] = $this->makeField(
                $this->hasCheckboxChecked($sections['harta'], 'Tanah') ? 'Ya' : null,
                $this->hasCheckboxChecked($sections['harta'], 'Tanah') ? 85 : 70,
                'Tanah'
            );
            $fields['harta_kereta'] = $this->makeField(
                $this->hasCheckboxChecked($sections['harta'], 'Kereta') ? 'Ya' : null,
                $this->hasCheckboxChecked($sections['harta'], 'Kereta') ? 85 : 70,
                'Kereta'
            );
            $fields['harta_motosikal'] = $this->makeField(
                $this->hasCheckboxChecked($sections['harta'], 'Motosikal') ? 'Ya' : null,
                $this->hasCheckboxChecked($sections['harta'], 'Motosikal') ? 85 : 70,
                'Motosikal'
            );
        }

        $jenisBantuan = $this->detectJenisBantuan($text);
        foreach ($jenisBantuan as $key => $val) {
            $fields[$key] = $this->makeField($val, $val !== null ? 85 : 70, 'Jenis Bantuan');
        }

        return $fields;
    }

    /**
     * Detect which bantuan types (Sara Hidup, Perubatan, Berdikari, Perumahan, Hutang)
     * are checked in Section 3, and extract their amounts.
     */
    private function detectJenisBantuan(string $text): array
    {
        $result = [];

        $bantuanTypes = [
            'bantuan_sara_hidup' => ['Bantuan Sara Hidup', 'Sara Hidup'],
            'bantuan_perubatan' => ['Bantuan Perubatan', 'Perubatan'],
            'bantuan_berdikari' => ['Bantuan Berdikari', 'Berdikari'],
            'bantuan_perumahan' => ['Bantuan Perumahan', 'Perumahan'],
            'menjelaskan_hutang' => ['Menjelaskan Hutang', 'Hutang'],
        ];

        foreach ($bantuanTypes as $key => $labels) {
            $hasChecked = false;
            foreach ($labels as $label) {
                if ($this->hasCheckboxChecked($text, $label)) {
                    $hasChecked = true;
                    break;
                }
            }
            $result[$key] = $hasChecked ? 'Ya' : null;
        }

        return $result;
    }

    /**
     * Parse the Tanggungan/Waris table (Section 6) into household_members rows.
     */
    private function recognizeTanggunganTable(string $text, array $existing): array
    {
        $sectionText = $this->extractSectionText($text, ['MAKLUMAT TANGGUNGAN', 'MAKLUMAT TANGGUNGAN ATAU WARIS']);
        if ($sectionText === null) {
            return $existing;
        }

        $rows = [];
        $lines = explode("\n", $sectionText);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || preg_match('/^[\d]+[\.\)]\s*$/', $trimmed)) {
                continue;
            }
            if (preg_match('/(Nama Penuh|No\.? Kad Pengenalan|Sekolah|Pekerjaan|Pendapatan|Hubungan|Tinggal)/i', $trimmed)) {
                continue;
            }

            $icDigits = preg_replace('/\D/', '', $trimmed);
            $hasIc = preg_match('/\d{6}[\s\-]?\d{2}[\s\-]?\d{4}/', $trimmed) || strlen($icDigits) >= 10;

            if ($hasIc) {
                $cleanIc = preg_replace('/\D/', '', $trimmed);
                $cleanIc = substr($cleanIc, 0, 12);
                $member = [
                    'nama' => null,
                    'ic' => $cleanIc,
                    'hubungan' => null,
                    'pekerjaan' => null,
                    'pendapatan_bulanan' => null,
                    'tinggal_bersama' => null,
                    'confidence' => 65,
                ];

                if (preg_match('/(Suami|Isteri|Anak|Ibu|Bapa|Adik|Kakak|Abang|Cucu|Menantu)/i', $trimmed, $m)) {
                    $member['hubungan'] = $m[1];
                }

                $parts = preg_split('/\s{2,}|\|/', $trimmed);
                if (count($parts) > 1) {
                    $member['nama'] = trim($parts[0]);
                } else {
                    $namePart = preg_replace('/\d.*$/', '', $trimmed);
                    $member['nama'] = trim($namePart) ?: null;
                }

                if (preg_match('/\b(\d{2,5})\b/', $trimmed, $m) && (int) $m[1] > 100) {
                    $member['pendapatan_bulanan'] = (int) $m[1];
                }

                $rows[] = $member;
            } elseif (strlen($trimmed) > 3 && ! preg_match('/^[\d\.\)\s]+$/', $trimmed)) {
                $rows[] = [
                    'nama' => $trimmed,
                    'ic' => null,
                    'hubungan' => null,
                    'pekerjaan' => null,
                    'pendapatan_bulanan' => null,
                    'tinggal_bersama' => null,
                    'confidence' => 50,
                ];
            }
        }

        if (empty($rows)) {
            return $existing;
        }

        return $rows;
    }

    /**
     * Parse Section 10 (Pendapatan & Perbelanjaan) and Section 8 (Harta), Section 9 (Pekerjaan).
     */
    private function recognizeFinancialTables(string $text): array
    {
        $result = [
            'pendapatan' => [],
            'perbelanjaan' => [],
            'harta' => [],
            'pekerjaan' => [],
        ];

        $pendapatanSection = $this->extractSectionText($text, ['MAKLUMAT PENDAPATAN & PERBELANJAAN', 'MAKLUMAT PENDAPATAN DAN PERBELANJAAN']);
        if ($pendapatanSection !== null) {
            $pendapatanRows = [
                'pendapatan_diri' => 'Diri',
                'pendapatan_pasangan_ibubapa_penjaga' => 'Pasangan',
                'pendapatan_pencen_perkeso' => 'Pencen',
                'pendapatan_sumbangan_anak' => 'Sumbangan Anak',
                'pendapatan_bantuan_agensi' => 'Bantuan',
                'pendapatan_lain_lain' => 'Lain-lain',
            ];
            foreach ($pendapatanRows as $field => $keyword) {
                $value = $this->extractRowAmount($pendapatanSection, $keyword);
                if ($value !== null) {
                    $result['pendapatan'][$field] = $value;
                }
            }

            $perbelanjaanRows = [
                'perbelanjaan_makan_minum' => 'Makan',
                'perbelanjaan_bil_api_air' => 'Bil',
                'perbelanjaan_persekolahan' => 'Persekolahan',
                'pengangkutan' => 'Pengangkutan',
                'perubatan' => 'Perubatan',
                'perbelanjaan_sewa_ansuran' => 'Sewa',
                'perbelanjaan_lain_lain' => 'Lain-lain',
            ];
            foreach ($perbelanjaanRows as $field => $keyword) {
                $value = $this->extractRowAmount($pendapatanSection, $keyword);
                if ($value !== null) {
                    $result['perbelanjaan'][$field] = $value;
                }
            }
        }

        $hartaSection = $this->extractSectionText($text, ['MAKLUMAT HARTA']);
        if ($hartaSection !== null) {
            $hartaRows = [
                'harta_rumah' => 'Rumah',
                'harta_tanah' => 'Tanah',
                'harta_kereta' => 'Kereta',
                'harta_motosikal' => 'Motosikal',
            ];
            foreach ($hartaRows as $field => $keyword) {
                $value = $this->extractRowAmount($hartaSection, $keyword);
                if ($value !== null) {
                    $result['harta'][$field] = $value;
                }
            }
        }

        $pekerjaanSection = $this->extractSectionText($text, ['MAKLUMAT PEKERJAAN PEMOHON/PASANGAN', 'MAKLUMAT PEKERJAAN']);
        if ($pekerjaanSection !== null) {
            $pemohon = $this->extractPekerjaanRow($pekerjaanSection, 'Pemohon');
            $pasangan = $this->extractPekerjaanRow($pekerjaanSection, 'Pasangan');
            if ($pemohon) {
                $result['pekerjaan']['pemohon'] = $pemohon;
            }
            if ($pasangan) {
                $result['pekerjaan']['pasangan'] = $pasangan;
            }
        }

        return $result;
    }

    /**
     * Detect all checkboxes in the document and return their positions.
     * Returns array keyed by the option text.
     */
    private function detectCheckboxes(string $text): array
    {
        $checked = [];
        $unchecked = [];

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/[☑✓✔]\s*(.+)/u', $trimmed, $m)) {
                $checked[] = trim($m[1]);
            }
            if (preg_match('/[☐✗✘]\s*(.+)/u', $trimmed, $m)) {
                $unchecked[] = trim($m[1]);
            }
        }

        return [
            'checked' => $checked,
            'unchecked' => $unchecked,
        ];
    }

    /**
     * Split OCR text into sections by their header labels.
     */
    private function splitSections(string $text, array $sectionMap): array
    {
        $sections = [];
        $lines = explode("\n", $text);
        $currentKey = null;
        $currentLines = [];

        foreach ($lines as $line) {
            $lineLower = mb_strtolower($line);
            $matchedKey = null;
            foreach ($sectionMap as $key => $marker) {
                if (mb_strpos($lineLower, mb_strtolower($marker)) !== false) {
                    $matchedKey = $key;
                    break;
                }
            }

            if ($matchedKey !== null) {
                if ($currentKey !== null) {
                    $sections[$currentKey] = implode("\n", $currentLines);
                }
                $currentKey = $matchedKey;
                $currentLines = [];
            } else {
                $currentLines[] = $line;
            }
        }

        if ($currentKey !== null) {
            $sections[$currentKey] = implode("\n", $currentLines);
        }

        return $sections;
    }

    /**
     * Extract text from a section by its header.
     */
    private function extractSectionText(string $text, array $markers): ?string
    {
        $lines = explode("\n", $text);
        $startIndex = null;
        foreach ($lines as $i => $line) {
            $lower = mb_strtolower($line);
            foreach ($markers as $marker) {
                if (mb_strpos($lower, mb_strtolower($marker)) !== false) {
                    $startIndex = $i + 1;
                    break 2;
                }
            }
        }

        if ($startIndex === null) {
            return null;
        }

        $sectionLines = [];
        foreach ($lines as $i => $line) {
            if ($i < $startIndex) {
                continue;
            }
            if ($i > $startIndex && $this->isNewSectionHeader($line)) {
                break;
            }
            $sectionLines[] = $line;
        }

        return implode("\n", $sectionLines);
    }

    private function isNewSectionHeader(string $line): bool
    {
        $headers = [
            'MAKLUMAT TAMBAHAN', 'MAKLUMAT TEMPAT TINGGAL', 'MAKLUMAT HARTA',
            'MAKLUMAT PEKERJAAN', 'MAKLUMAT PENDAPATAN', 'MAKLUMAT PERBELANJAAN',
            'MAKLUMAT TANGGUNGAN', 'PENGAKUAN PEMOHON', 'BUTIRAN PENGARUH',
            'PENGESAHAN', 'MAKLUMAT PEMOHON', 'MAKLUMAT PENGENALAN',
        ];
        foreach ($headers as $h) {
            if (mb_stripos($line, $h) !== false) {
                return true;
            }
        }

        return false;
    }

    private function hasCheckboxChecked(string $text, string $option): bool
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $lower = mb_strtolower($line);
            $optionLower = mb_strtolower($option);
            if (mb_strpos($lower, $optionLower) === false) {
                continue;
            }
            $optionQuoted = preg_quote($optionLower, '/');
            if (preg_match('/[☑✓✔][\]\s]*\s*'.$optionQuoted.'/u', $lower)) {
                return true;
            }
            if (preg_match('/\[\s*x\s*\][\]\s]*\s*'.$optionQuoted.'/i', $lower)) {
                return true;
            }
            if (preg_match('/\[\s*✓\s*\][\]\s]*\s*'.$optionQuoted.'/i', $lower)) {
                return true;
            }
            if (preg_match('/\bx\s+'.$optionQuoted.'/i', $lower)) {
                return true;
            }
        }

        return false;
    }

    private function detectCheckedOption(string $text, array $options): ?string
    {
        foreach ($options as $option) {
            if ($this->hasCheckboxChecked($text, $option)) {
                return $option;
            }
        }

        return null;
    }

    private function extractLabeledValue(string $text, string $label): ?string
    {
        $lines = explode("\n", $text);
        $labelLower = mb_strtolower($label);
        foreach ($lines as $i => $line) {
            $lower = mb_strtolower($line);
            if (mb_strpos($lower, $labelLower) === false) {
                continue;
            }
            $after = trim(mb_substr($line, mb_strlen($label)));
            $after = ltrim($after, ': :. ');
            if ($after !== '' && mb_strlen($after) > 1) {
                return $after;
            }
            if (isset($lines[$i + 1])) {
                $next = trim($lines[$i + 1]);
                if ($next !== '' && ! $this->isLikelyLabel($next)) {
                    return $next;
                }
            }
        }

        return null;
    }

    private function extractAmount(string $text): ?string
    {
        $matches = [];
        if (preg_match('/RM\s*([\d,]+(?:\.\d{1,2})?)/i', $text, $matches)) {
            return $this->normalizeAmount($matches[1]);
        }
        if (preg_match('/(\d{2,7}(?:\.\d{1,2})?)/', $text, $matches)) {
            return $this->normalizeAmount($matches[1]);
        }

        return null;
    }

    private function extractRowAmount(string $text, string $rowLabel): ?string
    {
        $lines = explode("\n", $text);
        $labelLower = mb_strtolower($rowLabel);
        foreach ($lines as $line) {
            $lower = mb_strtolower($line);
            if (mb_strpos($lower, $labelLower) === false) {
                continue;
            }
            if (preg_match('/RM\s*([\d,]+(?:\.\d{1,2})?)/i', $line, $m)) {
                return $this->normalizeAmount($m[1]);
            }
            if (preg_match('/(\d{2,7}(?:\.\d{1,2})?)/', $line, $m)) {
                return $this->normalizeAmount($m[1]);
            }
        }

        return null;
    }

    private function extractPekerjaanRow(string $text, string $who): ?array
    {
        $lines = explode("\n", $text);
        $whoLower = mb_strtolower($who);
        $capture = false;
        $row = [];

        foreach ($lines as $line) {
            $lower = mb_strtolower($line);
            if (mb_strpos($lower, $whoLower) !== false) {
                $capture = true;
            }
            if ($capture) {
                $row[] = $line;
                if (count($row) > 6) {
                    break;
                }
            }
            if ($capture && mb_strpos($lower, 'pasangan') !== false && mb_strpos($lower, $whoLower) === false) {
                array_pop($row);
                break;
            }
        }

        if (empty($row)) {
            return null;
        }

        $joined = implode("\n", $row);

        return [
            'nama' => $this->extractLabeledValue($joined, 'Nama'),
            'no_kp' => $this->extractLabeledValue($joined, 'No. K/P'),
            'majikan' => $this->extractLabeledValue($joined, 'Majikan'),
            'jawatan' => $this->extractLabeledValue($joined, 'Pekerjaan'),
            'pendapatan' => $this->extractAmount($joined),
        ];
    }

    private function normalizeAmount(string $value): string
    {
        $cleaned = preg_replace('/[^\d.]/', '', $value);
        if ($cleaned === '' || ! is_numeric($cleaned)) {
            return $value;
        }

        return number_format((float) $cleaned, 2, '.', '');
    }

    private function isLikelyLabel(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '-' || $trimmed === ':') {
            return true;
        }
        foreach (FieldSchema::$labels as $variants) {
            foreach ($variants as $label) {
                if (mb_strlen($label) > 3 && mb_stripos($trimmed, $label) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function makeField(?string $value, int $confidence, string $sourceLabel): array
    {
        $hasValue = $value !== null && $value !== '';

        return [
            'value' => $hasValue ? $value : null,
            'confidence' => $hasValue ? $confidence : 0,
            'source_label' => $sourceLabel,
            'needs_review' => ! $hasValue || $confidence < $this->confidenceThreshold,
        ];
    }

    private function mergeFields(array $existing, array $new): array
    {
        foreach ($new as $key => $entry) {
            $existingValue = $existing[$key] ?? null;
            $newValue = $entry['value'] ?? null;
            $existingHasValue = $existingValue && ($existingValue['value'] ?? null) !== null;
            $newHasValue = $newValue !== null;

            if ($newHasValue && (! $existingHasValue || ($entry['confidence'] ?? 0) > ($existingValue['confidence'] ?? 0))) {
                $existing[$key] = $entry;
            } elseif (! $existingHasValue && ! $newHasValue) {
                $existing[$key] = $entry;
            }
        }

        return $existing;
    }
}
