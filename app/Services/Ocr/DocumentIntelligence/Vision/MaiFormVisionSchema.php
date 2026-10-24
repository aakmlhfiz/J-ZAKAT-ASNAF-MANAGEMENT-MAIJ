<?php

namespace App\Services\Ocr\DocumentIntelligence\Vision;

/**
 * Builds the system + user prompt for the vision document-understanding model.
 * Embeds the MAIJ form structure (sections, field keys + types, checkbox
 * groups, dependents table, signature/date zones) and enforces a strict JSON
 * output schema with per-field confidence, page and bounding box, plus the
 * "never guess" rule (unreadable → null + needs_review).
 */
class MaiFormVisionSchema
{
    public const DOCUMENT_TYPE = 'MAIJ Zakat Assistance Application Form';

    /**
     * Section → field key → human label. Checkbox-derived attributes are
     * declared separately (see checkboxGroups()).
     *
     * @return array<string, array<string, string>>
     */
    public static function sections(): array
    {
        return [
            'applicant' => [
                'ic' => 'No. K/P Baru/Polis/Tentera/No. Pasport',
                'nama' => 'Nama Pemohon',
                'alamat' => 'Alamat Surat Menyurat',
                'daerah' => 'Daerah',
                'poskod' => 'Poskod',
                'bandar' => 'Bandar',
                'dun' => 'DUN',
                'parlimen' => 'Parlimen',
                'no_telefon_bimbit' => 'No. Telefon Bimbit',
                'no_telefon_rumah' => 'No. Telefon Rumah',
                'kesihatan' => 'Kesihatan (Sihat/Sakit/Cacat)',
                'status_perkahwinan' => 'Status Perkahwinan (Bujang/Berkahwin/Janda/Duda)',
                'merokok' => 'Merokok (Ya/Tidak)',
            ],
            'sara_hidup' => [
                'jumlah_sara_hidup' => 'Jumlah Sara Hidup (RM)',
                'no_akaun_bimb' => 'No. Akaun BIMB',
                'bantuan_agensi_lain_ada' => 'Bantuan daripada Agensi lain (Ada)',
                'bantuan_agensi_lain_nyatakan' => 'Agensi lain (Nyatakan)',
                'bantuan_agensi_lain_jumlah' => 'Agensi lain (RM)',
                'tidak_pernah_terima_bantuan' => 'Tidak pernah menerima bantuan dari agensi lain',
            ],
            'medical' => [
                'perubatan_jenis_rawatan' => 'Jenis Rawatan (Dialisis/Pelbagai)',
                'perubatan_pemohon_or_tanggungan' => 'Rawatan untuk (Pemohon/Tanggungan)',
                'perubatan_nama_tanggungan' => 'Nama Tanggungan',
                'perubatan_hubungan_tanggungan' => 'Hubungan',
                'perubatan_no_kp_tanggungan' => 'No. Kad Pengenalan Tanggungan',
                'perubatan_jenis_penyakit' => 'Jenis Penyakit',
                'perubatan_mula_mengidap' => 'Mula Mengidap',
                'perubatan_tempat_rawatan' => 'Tempat Rawatan',
                'perubatan_kos_rawatan' => 'Kos Rawatan (RM)',
                'perubatan_peralatan' => 'Peralatan Diperlukan',
                'perubatan_harga_alatan' => 'Harga Alatan (RM)',
            ],
            'business' => [
                'jenis_berdikari' => 'Jenis Berdikari (Peralatan/Modal)',
                'berdikari_jenis_peralatan' => 'Jenis peralatan yang dipohon',
                'berdikari_harga_peralatan' => 'Harga Peralatan/Modal (RM)',
                'berdikari_jenis_perniagaan' => 'Jenis Perniagaan',
                'berdikari_sebab_memerlukan' => 'Sebab memerlukan',
                'berdikari_pengalaman_ada' => 'Pengalaman Berniaga (Ada/Tiada)',
                'berdikari_pengalaman_tahun' => 'Pengalaman (berapa tahun)',
            ],
            'housing' => [
                'jenis_bantuan_perumahan' => 'Bantuan Perumahan (Bina Baru/Baik Pulih)',
                'status_tanah' => 'Status Tanah (Sendiri/Sewa/Tumpang)',
                'nama_dalam_geran' => 'Nama dalam Geran',
                'no_geran' => 'No. Geran',
                'perumahan_laporan_polis' => 'Laporan Polis (Ada/Tiada)',
            ],
            'debt' => [
                'sebab_berhutang' => 'Sebab berhutang',
                'jumlah_hutang' => 'Jumlah Hutang (RM)',
                'sebab_gagal_bayar_hutang' => 'Sebab gagal bayar hutang',
                'nama_pemberi_hutang' => 'Nama Pemberi Hutang',
                'no_kp_pemberi_hutang' => 'No. K/P Pemberi Hutang',
            ],
            'residence' => [
                'tempat_tinggal' => 'Tempat Tinggal (Sendiri/Menumpang/Menyewa/Lain-lain)',
                'nilai_tempat_tinggal' => 'Nilai/Sewa (RM)',
                'lain_lain_nyatakan_tempat_tinggal' => 'Lain-lain Nyatakan',
            ],
            'assets' => [
                'harta_rumah' => 'Harta Rumah (ada/tiada)',
                'harta_unit_rumah' => 'Harta Rumah (Unit)',
                'harta_nilai_rumah' => 'Harta Rumah (Nilai RM)',
                'harta_tanah' => 'Harta Tanah (ada/tiada)',
                'harta_ekar' => 'Harta Tanah (Ekar)',
                'harta_nilai_tanah' => 'Harta Tanah (Nilai RM)',
                'harta_kereta' => 'Harta Kereta (ada/tiada)',
                'harta_unit_kereta' => 'Harta Kereta (Unit)',
                'harta_nilai_kereta' => 'Harta Kereta (Nilai RM)',
                'harta_motosikal' => 'Harta Motosikal (ada/tiada)',
                'harta_unit_motosikal' => 'Harta Motosikal (Unit)',
                'harta_nilai_motosikal' => 'Harta Motosikal (Nilai RM)',
            ],
            'employment' => [
                'pekerjaan_pemohon_nama' => 'Pemohon Nama',
                'pekerjaan_pemohon_no_kp' => 'Pemohon No. K/P',
                'pekerjaan_pemohon_majikan' => 'Pemohon Majikan',
                'pekerjaan_pemohon_jawatan' => 'Pemohon Pekerjaan',
                'pekerjaan_pemohon_pendapatan' => 'Pemohon Pendapatan (RM)',
                'pekerjaan_pasangan_nama' => 'Pasangan Nama',
                'pekerjaan_pasangan_no_kp' => 'Pasangan No. K/P',
                'pekerjaan_pasangan_majikan' => 'Pasangan Majikan',
                'pekerjaan_pasangan_jawatan' => 'Pasangan Pekerjaan',
                'pekerjaan_pasangan_pendapatan' => 'Pasangan Pendapatan (RM)',
            ],
            'income' => [
                'pendapatan_diri' => 'Pendapatan Diri',
                'pendapatan_pasangan_ibubapa_penjaga' => 'Pendapatan Pasangan/Ibubapa/Penjaga',
                'pendapatan_pencen_perkeso' => 'Pendapatan Pencen/PERKESO',
                'pendapatan_sumbangan_anak' => 'Pendapatan Sumbangan Anak-anak',
                'pendapatan_bantuan_agensi' => 'Pendapatan Bantuan daripada agensi lain',
                'pendapatan_lain_lain' => 'Pendapatan Lain-lain',
                'jumlah_pendapatan' => 'JUMLAH PENDAPATAN (RM)',
            ],
            'expenses' => [
                'perbelanjaan_makan_minum' => 'Makan/Minum',
                'perbelanjaan_bil_api_air' => 'Bil Api/Air',
                'perbelanjaan_persekolahan' => 'Persekolahan',
                'pengangkutan' => 'Pengangkutan',
                'perubatan' => 'Perubatan',
                'perbelanjaan_sewa_ansuran' => 'Sewa/Ansuran Rumah',
                'perbelanjaan_lain_lain' => 'Lain-lain',
                'jumlah_perbelanjaan' => 'JUMLAH PERBELANJAAN (RM)',
            ],
            'declaration' => [
                'pengakuan_nama' => 'Nama Pengakuan',
                'pengakuan_tarikh' => 'Tarikh Pengakuan',
            ],
            'verification' => [
                'pengesah_nama' => 'Nama Pengesah',
                'pengesah_jawatan' => 'Jawatan Pengesah',
                'pengesah_ulasan' => 'Ulasan',
                'pengesah_tarikh' => 'Tarikh Pengesah',
            ],
        ];
    }

    /**
     * Field key → value type hint for the model (helps IC/amount/date/phone).
     *
     * @return array<string, string>
     */
    public static function fieldTypes(): array
    {
        return [
            'ic' => 'ic', 'no_kp_lain' => 'ic', 'perubatan_no_kp_tanggungan' => 'ic', 'no_kp_pemberi_hutang' => 'ic',
            'no_telefon_bimbit' => 'phone', 'no_telefon_rumah' => 'phone',
            'poskod' => 'poskod',
            'perubatan_mula_mengidap' => 'date', 'pengakuan_tarikh' => 'date', 'pengesah_tarikh' => 'date',
            'jumlah_sara_hidup' => 'amount', 'bantuan_agensi_lain_jumlah' => 'amount',
            'perubatan_kos_rawatan' => 'amount', 'perubatan_harga_alatan' => 'amount',
            'berdikari_harga_peralatan' => 'amount', 'jumlah_hutang' => 'amount',
            'nilai_tempat_tinggal' => 'amount', 'harta_nilai_rumah' => 'amount', 'harta_nilai_tanah' => 'amount',
            'harta_nilai_kereta' => 'amount', 'harta_nilai_motosikal' => 'amount',
            'pekerjaan_pemohon_pendapatan' => 'amount', 'pekerjaan_pasangan_pendapatan' => 'amount',
            'pendapatan_diri' => 'amount', 'pendapatan_pasangan_ibubapa_penjaga' => 'amount',
            'pendapatan_pencen_perkeso' => 'amount', 'pendapatan_sumbangan_anak' => 'amount',
            'pendapatan_bantuan_agensi' => 'amount', 'pendapatan_lain_lain' => 'amount', 'jumlah_pendapatan' => 'amount',
            'perbelanjaan_makan_minum' => 'amount', 'perbelanjaan_bil_api_air' => 'amount',
            'perbelanjaan_persekolahan' => 'amount', 'pengangkutan' => 'amount', 'perubatan' => 'amount',
            'perbelanjaan_sewa_ansuran' => 'amount', 'perbelanjaan_lain_lain' => 'amount', 'jumlah_perbelanjaan' => 'amount',
        ];
    }

    /**
     * Checkbox groups and their options for tri-state detection.
     *
     * @return array<string, array<string>>
     */
    public static function checkboxGroups(): array
    {
        return [
            'kesihatan' => ['sihat', 'sakit', 'cacat'],
            'status_perkahwinan' => ['bujang', 'berkahwin', 'janda', 'duda'],
            'merokok' => ['ya', 'tidak'],
            'perubatan_jenis_rawatan' => ['dialisis', 'pelbagai'],
            'perubatan_pemohon_or_tanggungan' => ['pemohon', 'tanggungan'],
            'jenis_berdikari' => ['peralatan', 'modal'],
            'berdikari_pengalaman_ada' => ['ada', 'tiada'],
            'status_tanah' => ['sendiri', 'sewa', 'tumpang'],
            'perumahan_laporan_polis' => ['ada', 'tiada'],
            'tempat_tinggal' => ['sendiri', 'menumpang', 'menyewa', 'lain_lain'],
            'harta' => ['rumah', 'tanah', 'kereta', 'motosikal'],
        ];
    }

    /**
     * @return array{system:string, user:string}
     */
    public function build(): array
    {
        return [
            'system' => $this->systemPrompt(),
            'user' => $this->userPrompt(),
        ];
    }

    private function systemPrompt(): string
    {
        return 'You are a document-understanding engine for the Majlis Agama Islam Johor (MAIJ) Zakat Assistance Application Form (Borang Agihan Zakat). You receive the rendered pages of a filled 4-page MAIJ application form. Understand the complete form layout: for every printed field label, read the value that was written inside its field (typed or handwritten). Read checkboxes (ticked vs empty), tables row-by-row preserving columns, signature/date areas, and printed section headers. You always answer with ONLY valid JSON. Never invent a value that is not visible. If a value cannot be read with confidence, return value null and needs_review true. Respond in the language the form uses (Malay).';
    }

    private function userPrompt(): string
    {
        $lines = [];
        $lines[] = 'This is a filled '.self::DOCUMENT_TYPE.' (4 pages). Read each page image carefully.';
        $lines[] = 'Map each printed FIELD LABEL to the VALUE written inside its field.';
        $lines[] = '';
        $lines[] = 'Return ONLY valid JSON with this exact structure:';
        $lines[] = json_encode($this->schemaExample(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $lines[] = '';
        $lines[] = 'SECTION FIELD KEYS (use these exact keys; only include fields that are present/visible on the form):';

        foreach (self::sections() as $section => $fields) {
            $lines[] = '- '.$section.': '.implode(', ', array_map(
                fn ($key, $label) => $key.' ('.$label.')',
                array_keys($fields),
                array_values($fields)
            ));
        }

        $lines[] = '';
        $lines[] = 'CHECKBOX GROUPS (return each option as true (ticked), false (empty) or null (unclear)):';
        foreach (self::checkboxGroups() as $group => $options) {
            $lines[] = '- '.$group.': '.implode(', ', $options);
        }

        $lines[] = '';
        $lines[] = 'DEPENDENTS (Maklumat Tanggungan/Waris, page 3): return an array "dependents" with up to 6 rows, each {row, nama_penuh, no_ic, sekolah_pekerjaan, pendapatan, hubungan, tinggal_bersama}. Blank rows → null fields.';
        $lines[] = 'APPLICATIONS (Maklumat Permohonan Bantuan, page 1): return array "applications" with one object per bantuan type: {type, selected, amount_requested, reason, page}.';
        $lines[] = 'SIGNATURE/STAMP: "signature" = {present: bool|null, confidence}; "stamp" = {present: bool|null, confidence}. Do NOT convert a signature into a name.';
        $lines[] = 'SECTIONS DETECTED: "sections_detected" = list of printed section titles you recognised (e.g. "4. MAKLUMAT PENGENALAN PEMOHON").';
        $lines[] = '';
        $lines[] = 'PER-FIELD VALUE OBJECT — every field value must be an object: {"value": string|null, "confidence": 0-100, "page": 1-4, "input_type": "printed"|"typed"|"handwritten"|null, "bbox": {"x": int,"y": int,"width": int,"height": int}|null, "needs_review": bool}.';
        $lines[] = '  - bbox: normalized coordinates 0-1000 relative to the page, only when you can localise the value.';
        $lines[] = '  - needs_review: true when confidence < 60 or the value is unreadable.';
        $lines[] = '';
        $lines[] = 'RULES:';
        $lines[] = '  1. Do NOT return a raw OCR text dump — only the structured JSON above.';
        $lines[] = '  2. Never guess names, ICs, phone numbers or amounts. Unreadable → value null, needs_review true.';
        $lines[] = '  3. IC = 12 digits. Amounts = numbers without "RM". Dates = keep readable (e.g. "12/08/2026" or "Feb 2026").';
        $lines[] = '  4. Preserve the FIELD LABEL → FIELD VALUE relationship exactly.';
        $lines[] = '  5. Preserve "S.D.A." verbatim if that is what is written.';

        return implode("\n", $lines);
    }

    private function schemaExample(): array
    {
        return [
            'document' => ['pages' => 4, 'document_type' => self::DOCUMENT_TYPE, 'sections_detected' => ['4. MAKLUMAT PENGENALAN PEMOHON']],
            'applicant' => ['nama' => ['value' => null, 'confidence' => 0, 'page' => 1, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'applications' => [['type' => 'Bantuan Sara Hidup', 'selected' => false, 'amount_requested' => null, 'reason' => null, 'page' => 1]],
            'sara_hidup' => ['jumlah_sara_hidup' => ['value' => null, 'confidence' => 0, 'page' => 2, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'medical' => ['perubatan_nama_tanggungan' => ['value' => null, 'confidence' => 0, 'page' => 2, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'business' => ['jenis_berdikari' => ['value' => null, 'confidence' => 0, 'page' => 2, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'housing' => ['nama_dalam_geran' => ['value' => null, 'confidence' => 0, 'page' => 2, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'debt' => ['sebab_berhutang' => ['value' => null, 'confidence' => 0, 'page' => 2, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'dependents' => [['row' => 1, 'nama_penuh' => null, 'no_ic' => null, 'sekolah_pekerjaan' => null, 'pendapatan' => null, 'hubungan' => null, 'tinggal_bersama' => null]],
            'residence' => ['tempat_tinggal' => ['value' => null, 'confidence' => 0, 'page' => 3, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'assets' => ['harta_rumah' => ['value' => null, 'confidence' => 0, 'page' => 3, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'employment' => ['pekerjaan_pemohon_nama' => ['value' => null, 'confidence' => 0, 'page' => 3, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'income' => ['pendapatan_diri' => ['value' => null, 'confidence' => 0, 'page' => 3, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'expenses' => ['perbelanjaan_makan_minum' => ['value' => null, 'confidence' => 0, 'page' => 3, 'input_type' => null, 'bbox' => null, 'needs_review' => true]],
            'declaration' => ['pengakuan_nama' => ['value' => null, 'confidence' => 0, 'page' => 4, 'input_type' => null, 'bbox' => null, 'needs_review' => true], 'signature' => ['present' => null, 'confidence' => 0]],
            'verification' => ['pengesah_nama' => ['value' => null, 'confidence' => 0, 'page' => 4, 'input_type' => null, 'bbox' => null, 'needs_review' => true], 'stamp' => ['present' => null, 'confidence' => 0]],
            'checkboxes' => ['kesihatan' => ['sihat' => null, 'sakit' => null, 'cacat' => null]],
        ];
    }
}
