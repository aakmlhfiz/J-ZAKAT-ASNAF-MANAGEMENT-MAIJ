<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Orchestrates MAIJ form understanding for a 4-page Zakat Assistance
 * Application Form. Renders each page, reads word-coordinate OCR, maps filled
 * values into fields via {@see MaiFormTemplate}, detects checkboxes by ink
 * density, extracts tables (dependents / employment / income / expenses),
 * detects signatures, normalises values and returns the structured
 * `ocr_result` document.
 */
class BorangFormUnderstanding
{
    private const DOCUMENT_TYPE = 'MAIJ Zakat Assistance Application Form';

    private ValueNormalizer $normalizer;

    private InputTypeDetector $inputType;

    public function __construct(?ValueNormalizer $normalizer = null, ?InputTypeDetector $inputType = null)
    {
        $this->normalizer = $normalizer ?? new ValueNormalizer;
        $this->inputType = $inputType ?? new InputTypeDetector;
    }

    /**
     * @param  array{page_count?:int, has_text_layer?:bool, is_scanned?:bool, pages?:array}  $pageInfo
     * @return array<string, mixed>
     */
    public function understand(string $filePath, string $mimeType, string $ocrText, array $pageInfo): array
    {
        $grids = [];
        $pages = [];

        $extractor = new TesseractWordExtractor;
        if ($extractor->available()) {
            $pages = $extractor->extractPages($filePath, $mimeType);
        }

        foreach ($pages as $page) {
            $grids[$page['page']] = new WordGrid($page['words'], $page['width'], $page['height']);
        }

        $pageTexts = $this->splitPageTexts($ocrText);

        // Type-aware text-layer results for pages that do not rasterise.
        $textLayerResults = [];
        foreach (MaiFormTemplate::textLayerSpecs() as $page => $spec) {
            $text = $pageTexts[$page] ?? '';
            if (trim($text) !== '') {
                $textLayerResults[$page] = (new TextLayerSequenceParser)->parse($text, $page);
            }
        }

        $fields = [];
        $checkboxes = [];
        $density = function (int $x, int $y, int $w, int $h, int $page) use ($extractor, $pages): ?float {
            $info = $pages[$page - 1] ?? null;
            if (! $info) {
                return null;
            }
            $sx = $info['width'] / MaiFormTemplate::$referenceWidth;
            $sy = $info['height'] / MaiFormTemplate::$referenceHeight;

            return $extractor->density($page, (int) round($x * $sx), (int) round($y * $sy), max(1, (int) round($w * $sx)), max(1, (int) round($h * $sy)));
        };

        foreach (MaiFormTemplate::fields() as $key => $spec) {
            $page = $spec['page'];
            $grid = $grids[$page] ?? null;

            $value = null;
            $sourceType = 'ocr';
            $bbox = null;
            $zoneWords = [];

            if ($grid && ! $grid->isEmpty() && $spec['zone'] !== null) {
                [$zx, $zy, $zw, $zh] = $spec['zone'];
                $zoneWords = $grid->zoneWords($zx, $zy, $zw, $zh, 0);
                $value = $grid->zoneText($zx, $zy, $zw, $zh, 0);
                $bbox = ['x' => $zx, 'y' => $zy, 'width' => $zw, 'height' => $zh];
            }

            // Text-layer fallback for pages that did not rasterise.
            $textLayerConf = null;
            if (($value === null || trim($value) === '') && isset($textLayerResults[$page][$key]['value'])) {
                $value = $textLayerResults[$page][$key]['value'];
                $sourceType = 'text_layer';
                $textLayerConf = (int) ($textLayerResults[$page][$key]['confidence'] ?? 0);
            }

            $fields[$key] = $this->buildField($spec, $value, $zoneWords, $page, $bbox, $sourceType, $textLayerConf);
        }

        // Date fields: fall back to a date token in the page's text layer when
        // the zone OCR did not produce one (e.g. faint handwriting).
        foreach (MaiFormTemplate::fields() as $key => $spec) {
            if ($spec['type'] !== 'date' || ($fields[$key]['value'] ?? null) !== null) {
                continue;
            }
            $pageText = $pageTexts[$spec['page']] ?? '';
            $date = $this->findDateInText($pageText);
            if ($date !== null) {
                $norm = $this->normalizer->normalize('date', $date, 'date');
                $fields[$key]['value'] = $norm['value'];
                $fields[$key]['original_value'] = $norm['original_value'];
                $fields[$key]['normalized_value'] = $norm['normalized_value'];
                $fields[$key]['confidence'] = 55;
                $fields[$key]['source_type'] = 'text_layer';
                $fields[$key]['needs_review'] = true;
            }
        }

        // Checkbox groups.
        foreach (MaiFormTemplate::checkboxGroups() as $groupKey => $group) {
            $group['key'] = $groupKey;
            $page = $group['page'];
            $grid = $grids[$page] ?? null;
            if (! $grid || $grid->isEmpty()) {
                $checkboxes[$groupKey] = $this->emptyCheckboxResult($group['options']);

                continue;
            }

            $detector = new CheckboxDetector(
                $grid,
                $page,
                $group,
                $density,
                MaiFormTemplate::fixedCheckboxZones(),
                MaiFormTemplate::checkboxBaselines()
            );
            $checkboxes[$groupKey] = $detector->detect();
        }

        // Ink zones (signature / stamp).
        $ink = [];
        foreach (MaiFormTemplate::inkZones() as $key => $zone) {
            $z = $zone['zone'];
            $ratio = $density($z['x'], $z['y'], $z['w'], $z['h'], $zone['page']);
            $baseline = (float) ($zone['baseline'] ?? 0.02);
            $threshold = ($baseline * 1.5) + 0.02;

            if ($ratio === null) {
                $ink[$key] = ['present' => null, 'confidence' => 0, 'density' => null, 'needs_review' => true];

                continue;
            }

            $present = $ratio >= $threshold;
            $confidence = $present
                ? min(85, 45 + (int) round(($ratio - $threshold) * 800))
                : 55;
            $ink[$key] = [
                'present' => $present,
                'confidence' => $confidence,
                'density' => $ratio,
                'needs_review' => true,
            ];
        }

        $dependents = $this->extractDependents($grids);
        $employment = $this->extractEmployment($grids);
        $income = $this->extractRowAmounts($grids, MaiFormTemplate::incomeRows());
        $expenses = $this->extractRowAmounts($grids, MaiFormTemplate::expenseRows());

        $sectionsDetected = $this->detectSections($grids, $pageTexts);
        $applications = $this->buildApplications($grids, $fields, $checkboxes);
        $applicantGroup = $this->groupApplicant($fields, $checkboxes);
        $financialAudit = (new FinancialAudit)->audit([
            'income' => $income,
            'expenses' => $expenses,
            'applicant' => $applicantGroup,
        ]);

        return [
            'source' => 'local',
            'provider' => 'template-anchored',
            'document' => [
                'pages' => (int) ($pageInfo['page_count'] ?? count($grids)),
                'document_type' => self::DOCUMENT_TYPE,
                'sections_detected' => $sectionsDetected,
            ],
            'fields' => $fields,
            'checkboxes' => $checkboxes,
            'applicant' => $applicantGroup,
            'applications' => $applications,
            'sara_hidup' => $this->groupByPrefix($fields, ['jumlah_sara_hidup', 'no_akaun_bimb', 'bantuan_agensi_lain_', 'tidak_pernah_terima_bantuan']),
            'medical' => $this->groupByPrefix($fields, 'perubatan_'),
            'business' => $this->groupByPrefix($fields, 'berdikari_'),
            'housing' => array_merge(
                $this->groupByPrefix($fields, 'perumahan_'),
                $this->groupByPrefix($fields, 'harta_'),
                $this->groupByPrefix($fields, 'nilai_tempat_tinggal'),
                $this->groupByPrefix($fields, 'tempat_tinggal_')
            ),
            'debt' => $this->groupByPrefix($fields, ['sebab_berhutang', 'jumlah_hutang', 'sebab_gagal_bayar_hutang', 'nama_pemberi_hutang', 'no_kp_pemberi_hutang']),
            'dependents' => $dependents,
            'residence' => array_merge(
                $this->groupByPrefix($fields, 'tempat_tinggal_'),
                $this->groupByPrefix($fields, 'nilai_tempat_tinggal'),
                $this->groupByPrefix($fields, 'lain_lain_nyatakan_tempat_tinggal')
            ),
            'assets' => [
                'harta' => $this->groupByPrefix($fields, 'harta_'),
                'tempat_tinggal' => array_merge(
                    $this->groupByPrefix($fields, 'tempat_tinggal_'),
                    $this->groupByPrefix($fields, 'nilai_tempat_tinggal'),
                    $this->groupByPrefix($fields, 'lain_lain_nyatakan_tempat_tinggal')
                ),
            ],
            'employment' => $employment,
            'income' => $income,
            'expenses' => $expenses,
            'financial_audit' => $financialAudit,
            'declaration' => array_merge(
                $this->groupByPrefix($fields, 'pengakuan_'),
                ['signature' => $ink['signature_pemohon'] ?? []]
            ),
            'verification' => array_merge(
                $this->groupByPrefix($fields, 'pengesah_'),
                ['stamp' => $ink['cop_pengesah'] ?? []]
            ),
        ];
    }

    private function stripLabelRemnant(?string $value, string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach (preg_split('/\s+/', trim($label)) as $word) {
            if (mb_strlen($word) < 3) {
                continue;
            }
            $value = preg_replace('/^\s*'.preg_quote($word, '/').'[\s:.\-]*/i', '', $value);
        }

        $value = preg_replace('/^\s*[:.\-]+\s*/', '', $value);

        return $value !== '' ? trim($value) : null;
    }

    private function plausiblePhone(string $value): bool
    {
        $digits = preg_replace('/\D/', '', $value);

        // Mobile: 01x-xxx xxxx (10-11 digits). Landline: 0x-xxx xxxx (9-10).
        return (bool) (preg_match('/^01\d{8,9}$/', $digits) || preg_match('/^0[3-9]\d{7,8}$/', $digits));
    }

    private function findDateInText(string $text): ?string
    {
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $m)) {
            return $m[1].'-'.$m[2].'-'.$m[3];
        }
        if (preg_match('/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/', $text, $m)) {
            return $m[1].'-'.$m[2].'-'.$m[3];
        }

        return null;
    }

    private function buildField(array $spec, ?string $value, array $zoneWords, int $page, ?array $bbox, string $sourceType, ?int $textLayerConf = null): array
    {
        $raw = $value !== null && trim($value) !== '' ? $value : null;

        if ($raw !== null && $spec['type'] === 'text') {
            $raw = $this->stripLabelRemnant($raw, $spec['label']);
        }

        $norm = $this->normalizer->normalize($spec['label'], $raw, $spec['type']);
        $input = $this->inputType->classify($zoneWords, $norm['value']);

        if ($sourceType === 'text_layer' && $textLayerConf !== null) {
            $confidence = $textLayerConf;
            $inputType = 'typed';
        } else {
            $confidence = $this->confidenceFor($spec['type'], $norm['value'], $input['confidence'], $sourceType, $zoneWords);
            $inputType = $input['input_type'];
        }

        return [
            'value' => $norm['value'],
            'original_value' => $norm['original_value'],
            'normalized_value' => $norm['normalized_value'],
            'confidence' => $confidence,
            'page' => $page,
            'bbox' => $bbox,
            'source_type' => $sourceType,
            'input_type' => $inputType,
            'needs_review' => $confidence < 60,
            'source_label' => $spec['label'],
        ];
    }

    private function confidenceFor(string $type, ?string $value, float $inputConfidence, string $sourceType, array $zoneWords): int
    {
        if ($value === null) {
            return 0;
        }

        $meanConf = 0.0;
        $confs = array_filter(array_map(fn ($w) => (float) $w['conf'], $zoneWords), fn ($c) => $c > 0);
        if (count($confs) > 0) {
            $meanConf = array_sum($confs) / count($confs);
        }

        if ($sourceType === 'text_layer') {
            return 45;
        }

        $base = $meanConf > 0 ? $meanConf : $inputConfidence;

        return match ($type) {
            'ic' => strlen((string) $value) === 12 ? (int) min(100, $base + 18) : (int) max(30, $base - 15),
            'amount', 'number' => (int) min(100, $base + 10),
            'poskod' => preg_match('/^\d{5}$/', (string) $value) ? (int) min(100, $base + 14) : (int) max(30, $base - 10),
            'phone' => $this->plausiblePhone((string) $value) ? (int) min(100, $base + 8) : (int) min(55, max(30, $base - 10)),
            default => (int) min(100, $base),
        };
    }

    private function splitPageTexts(string $ocrText): array
    {
        $pages = [];
        $current = null;
        foreach (explode("\n", $ocrText) as $line) {
            if (preg_match('/^---\s*PAGE\s+(\d+)\s*---$/', trim($line), $m)) {
                $current = (int) $m[1];
                $pages[$current] = '';

                continue;
            }
            if ($current !== null) {
                $pages[$current] .= $line."\n";
            }
        }

        return $pages;
    }

    private function emptyCheckboxResult(array $options): array
    {
        $out = [];
        foreach ($options as $key => $_opt) {
            $out[$key] = ['value' => null, 'confidence' => 0, 'needs_review' => true];
        }

        return $out;
    }

    private function extractDependents(array $grids): array
    {
        $def = MaiFormTemplate::dependentsTable();
        $grid = $grids[$def['page']] ?? null;
        if (! $grid || $grid->isEmpty()) {
            return [];
        }

        $rows = $this->clusterRows($grid, $def['y_top'], $def['y_bottom'], 45);
        $result = [];

        foreach ($rows as $i => $rowWords) {
            $cols = $this->assignColumns($rowWords, $def['columns']);
            $nama = $this->cleanCell($cols['nama'] ?? null);
            $nama = preg_replace('/^\d+[\s\.]*/', '', $nama); // strip row number
            $noIc = $this->cleanCell($cols['no_ic'] ?? null);
            $pendapatan = $this->cleanCell($cols['pendapatan'] ?? null);

            $icDigits = $noIc !== null ? preg_replace('/\D/', '', $noIc) : null;
            $pendDigits = $pendapatan !== null ? preg_replace('/\D/', '', $pendapatan) : null;

            $result[] = [
                'row' => $i + 1,
                'nama_penuh' => $nama ?: null,
                'no_ic' => $icDigits ? substr($icDigits, 0, 12) : null,
                'sekolah_pekerjaan' => $this->cleanCell($cols['sekolah_pekerjaan'] ?? null) ?: null,
                'pendapatan' => $pendDigits !== '' ? (int) $pendDigits : null,
                'hubungan' => $this->cleanCell($cols['hubungan'] ?? null) ?: null,
                'tinggal_bersama' => $this->cleanCell($cols['tinggal_bersama'] ?? null) ?: null,
                'confidence' => $nama || $icDigits ? 65 : 0,
                'needs_review' => true,
            ];
        }

        // Always emit 6 slots so blank rows surface as null fields.
        while (count($result) < 6) {
            $result[] = [
                'row' => count($result) + 1,
                'nama_penuh' => null,
                'no_ic' => null,
                'sekolah_pekerjaan' => null,
                'pendapatan' => null,
                'hubungan' => null,
                'tinggal_bersama' => null,
                'confidence' => 0,
                'needs_review' => false,
            ];
        }

        return $result;
    }

    private function extractEmployment(array $grids): array
    {
        $def = MaiFormTemplate::employmentTable();
        $grid = $grids[$def['page']] ?? null;
        if (! $grid || $grid->isEmpty()) {
            return [];
        }

        $out = [];
        foreach ($def['rows'] as $who => $row) {
            $band = $grid->zoneWords(100, $row['y'], MaiFormTemplate::$referenceWidth - 100, $row['h']);
            if (empty($band)) {
                $out[$who] = ['nama' => null, 'no_kp' => null, 'majikan' => null, 'pekerjaan' => null, 'pendapatan' => null, 'needs_review' => true];

                continue;
            }
            $cols = $this->assignColumns($band, $def['columns']);
            $out[$who] = [
                'nama' => ($cols['nama'] ?? null) ?: null,
                'no_kp' => ($cols['no_kp'] ?? null) ?: null,
                'majikan' => ($cols['majikan'] ?? null) ?: null,
                'pekerjaan' => ($cols['pekerjaan'] ?? null) ?: null,
                'pendapatan' => ($cols['pendapatan'] ?? null) ?: null,
                'needs_review' => true,
            ];
        }

        return $out;
    }

    private function extractRowAmounts(array $grids, array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $grid = $grids[3] ?? null;
            $value = null;
            $bbox = null;
            [$zx, $zy, $zw, $zh] = $row['amount_zone'];
            $colRight = $zx + $zw;

            if ($grid && ! $grid->isEmpty()) {
                $label = $grid->findLabel([$row['label']], 100, $row['y_top'], 1300, $row['y_bottom'] - $row['y_top']);
                if ($label) {
                    $after = $grid->wordsAfter($label, 0.4, $colRight);
                    $lastNum = null;
                    foreach ($after as $w) {
                        $cx = $w['l'] + (int) round($w['w'] / 2);
                        if ($cx < $zx) {
                            continue;
                        }
                        if (preg_match('/^\d[\d,\.]*$/', $w['text'])) {
                            $lastNum = $w['text'];
                        }
                    }
                    $value = $lastNum;
                    $bbox = ['x' => $zx, 'y' => $label['t'], 'width' => $zw, 'height' => $label['h']];
                }

                if ($value === null) {
                    $value = $grid->zoneText($zx, $zy, $zw, $zh, 0);
                    $bbox = ['x' => $zx, 'y' => $zy, 'width' => $zw, 'height' => $zh];

                    if ($value !== null && preg_match('/\d/', $value) !== 1) {
                        $value = null;
                    }
                }
            }

            $norm = $this->normalizer->normalize('pendapatan_'.$row['key'], $value, 'amount');
            $out[$row['key']] = [
                'source' => $row['label'],
                'value' => $norm['value'],
                'original_value' => $norm['original_value'],
                'confidence' => $norm['value'] !== null ? 65 : 0,
                'page' => 3,
                'bbox' => $bbox,
                'needs_review' => $norm['value'] === null,
            ];
        }

        return $out;
    }

    private function cleanCell(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }
        $cleaned = preg_replace('/^[\s|.]+|[\s|.]+$/', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return $cleaned !== '' ? trim($cleaned) : null;
    }

    private function clusterRows(WordGrid $grid, int $yTop, int $yBottom, int $tolerance): array
    {
        $band = $grid->zoneWords(0, $yTop, MaiFormTemplate::$referenceWidth, $yBottom - $yTop, 0);
        if (empty($band)) {
            return [];
        }

        usort($band, fn ($a, $b) => $a['t'] <=> $b['t'] ?: $a['l'] <=> $b['l']);

        $rows = [];
        $current = [];
        $lastTop = null;
        foreach ($band as $word) {
            if ($lastTop === null || abs($word['t'] - $lastTop) <= $tolerance) {
                $current[] = $word;
                $lastTop = $word['t'];
            } else {
                $rows[] = $current;
                $current = [$word];
                $lastTop = $word['t'];
            }
        }
        if (! empty($current)) {
            $rows[] = $current;
        }

        return $rows;
    }

    private function assignColumns(array $rowWords, array $columns): array
    {
        $out = [];
        foreach ($columns as $name => $col) {
            $x1 = $col['x'];
            $x2 = $col['x'] + $col['w'];
            $words = array_values(array_filter(
                $rowWords,
                fn ($w) => ($w['l'] + (int) round($w['w'] / 2)) >= $x1 && ($w['l'] + (int) round($w['w'] / 2)) <= $x2
            ));
            usort($words, fn ($a, $b) => $a['l'] <=> $b['l']);
            $out[$name] = trim(implode(' ', array_map(fn ($w) => $w['text'], $words))) ?: null;
        }

        return $out;
    }

    private function buildApplications(array $grids, array $fields, array $checkboxes): array
    {
        $types = [
            'sara_hidup' => 'Bantuan Sara Hidup',
            'perubatan' => 'Bantuan Perubatan',
            'berdikari' => 'Bantuan Berdikari',
            'perumahan' => 'Bantuan Perumahan',
            'hutang' => 'Menjelaskan Hutang',
        ];

        $grid = $grids[1] ?? null;
        $apps = [];

        foreach (MaiFormTemplate::bantuanRows() as $row) {
            $type = $row['key'];
            $amount = null;
            $reason = null;
            $bbox = null;

            if ($grid && ! $grid->isEmpty()) {
                [$zx, $zy, $zw, $zh] = $row['zone'];
                $rowText = $grid->zoneText($zx, $zy, $zw, $zh, 0);
                $bbox = ['x' => $zx, 'y' => $zy, 'width' => $zw, 'height' => $zh];

                if ($rowText !== null) {
                    $tokens = preg_split('/\s+/', trim($rowText));
                    $amountToken = null;
                    foreach ($tokens as $t) {
                        $clean = preg_replace('/^[|.\s]+|[|.\s]+$/', '', $t);
                        if ($clean !== '' && preg_match('/^\d[\d,\.]*$/', $clean)) {
                            $amountToken = $clean;
                            break;
                        }
                    }
                    if ($amountToken !== null) {
                        $amount = $this->normalizer->normalize('bantuan_amount', $amountToken, 'amount')['value'];
                        $reasonText = trim(str_replace($amountToken, '', $rowText));
                        $reason = $this->cleanBantuanReason($reasonText);
                    } else {
                        $reason = $this->cleanBantuanReason($rowText);
                    }
                }
            }

            // On this form the bantuan row is "ticked" by filling in the amount
            // (or the reason). Use that as the selection signal, not the box.
            $selected = $amount !== null ? true : ($reason !== null ? true : false);

            $apps[] = [
                'type' => $types[$type],
                'field' => 'bantuan_'.$type,
                'selected' => $selected,
                'amount_requested' => $amount,
                'reason' => $reason,
                'selection_source' => $amount !== null ? 'amount' : ($reason !== null ? 'reason' : 'empty'),
                'page' => 1,
                'bbox' => $bbox,
                'needs_review' => $amount === null,
            ];
        }

        return $apps;
    }

    private function cleanBantuanReason(?string $text): ?string
    {
        $cleaned = $this->cleanRowText($text);
        if ($cleaned === null) {
            return null;
        }

        // Reject static table tokens that are not applicant-filled reasons.
        if (preg_match('/^(rm|wajib diisi|diisi|jantina|ruangan|ruang|jumlah|.*5[a-eA-E].*)$/i', $cleaned)) {
            return null;
        }

        return $cleaned;
    }

    private function cleanRowText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }
        $cleaned = preg_replace('/^[\s|.]+|[\s|.]+$/', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return $cleaned !== '' ? trim($cleaned) : null;
    }

    private function groupApplicant(array $fields, array $checkboxes): array
    {
        $keys = ['nama', 'ic', 'alamat', 'daerah', 'poskod', 'bandar', 'dun', 'parlimen', 'no_telefon_bimbit', 'no_telefon_rumah'];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $fields[$key] ?? ['value' => null, 'confidence' => 0, 'needs_review' => true];
        }

        $kesihatan = $this->selectedCheckbox($checkboxes['kesihatan'] ?? []);
        $status = $this->selectedCheckbox($checkboxes['status_perkahwinan'] ?? []);
        $merokok = $this->selectedCheckbox($checkboxes['merokok'] ?? []);

        $out['kesihatan'] = $kesihatan;
        $out['status_perkahwinan'] = $status;
        $out['merokok'] = $merokok;

        return $out;
    }

    private function selectedCheckbox(array $group): array
    {
        $checked = [];
        foreach ($group as $key => $result) {
            if (($result['value'] ?? null) === true) {
                $checked[] = $key;
            }
        }

        if (count($checked) === 1) {
            return ['value' => $checked[0], 'confidence' => $group[$checked[0]]['confidence'] ?? 0, 'needs_review' => false];
        }

        return ['value' => null, 'confidence' => 0, 'needs_review' => true];
    }

    private function detectSections(array $grids, array $pageTexts): array
    {
        $found = [];
        foreach (MaiFormTemplate::sections() as $section) {
            $grid = $grids[$section['page']] ?? null;
            $text = $grid ? mb_strtolower($grid->plainText()) : '';
            if ($text === '' && isset($pageTexts[$section['page']])) {
                $text = mb_strtolower($pageTexts[$section['page']]);
            }
            if ($this->sectionMatches($text, $section['title'])) {
                $found[] = ['number' => $section['number'], 'title' => $section['title'], 'page' => $section['page']];
            }
        }

        return $found;
    }

    /**
     * Tolerant section-title match that survives common OCR spelling drift
     * (e.g. "PERMOHONAN" vs "PEMOHONAN"): lowercases, drops punctuation and
     * collapses repeated consonants before comparing.
     */
    private function sectionMatches(string $pageText, string $title): bool
    {
        $norm = function (string $s): string {
            $s = preg_replace('/[^a-z]/', '', mb_strtolower($s));
            $out = '';
            $prev = '';
            foreach (str_split($s) as $ch) {
                if ($ch !== $prev) {
                    $out .= $ch;
                }
                $prev = $ch;
            }

            return $out;
        };

        return mb_strpos($norm($pageText), $norm($title)) !== false;
    }

    private function groupByPrefix(array $fields, array|string $prefixes): array
    {
        $prefixes = (array) $prefixes;
        $out = [];
        foreach ($fields as $key => $field) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $out[$key] = $field;
                    break;
                }
            }
        }

        return $out;
    }
}
