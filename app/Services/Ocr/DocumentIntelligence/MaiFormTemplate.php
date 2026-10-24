<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Machine-readable map of the 4-page MAIJ Zakat Assistance Application Form
 * ("Borang Agihan Zakat"). Zone coordinates are defined in a reference space of
 * {@see $referenceWidth} x {@see $referenceHeight} pixels (A4 @ 220 DPI, the
 * resolution the real scanned forms render at). {@see WordGrid} normalises any
 * scan into this space so template zones match across resolutions.
 *
 * Coordinates were captured from the actual 4-page MAIJ forms (both blank and
 * filled) rendered at 220 DPI.
 */
class MaiFormTemplate
{
    public static int $referenceWidth = 1870;

    public static int $referenceHeight = 2420;

    /** @var array<string, mixed>|null */
    private static ?array $calibrationCache = null;

    /**
     * Scalar fields with a value zone. zone => [x, y, w, h] in reference space.
     *
     * @return array<string, array{page:int, section:string, label:string, zone:array|null, type:string}>
     */
    public static function fields(): array
    {
        return [
            // ---------------- PAGE 1 : MAKLUMAT PENGENALAN PEMOHON ----------------
            'ic' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'No. K/P Baru/Polis/Tentera/No. Pasport', 'zone' => [700, 1635, 1100, 55], 'type' => 'ic'],
            'nama' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Nama Pemohon', 'zone' => [330, 1700, 1500, 55], 'type' => 'text'],
            'alamat' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Alamat Surat Menyurat', 'zone' => [380, 1765, 1460, 55], 'type' => 'text'],
            'daerah' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Daerah', 'zone' => [250, 1898, 380, 50], 'type' => 'text'],
            'poskod' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Poskod', 'zone' => [800, 1898, 300, 50], 'type' => 'poskod'],
            'bandar' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Bandar', 'zone' => [1270, 1898, 500, 50], 'type' => 'text'],
            'dun' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'DUN', 'zone' => [250, 1965, 380, 50], 'type' => 'text'],
            'parlimen' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'Parlimen', 'zone' => [850, 1965, 380, 50], 'type' => 'text'],
            'no_telefon_bimbit' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'No. Telefon Bimbit', 'zone' => [460, 2030, 600, 55], 'type' => 'phone'],
            'no_telefon_rumah' => ['page' => 1, 'section' => 'MAKLUMAT PENGENALAN PEMOHON', 'label' => 'No. Telefon Rumah', 'zone' => [1150, 2030, 650, 55], 'type' => 'phone'],

            // ---------------- PAGE 2 : MAKLUMAT TAMBAHAN (5A SARA HIDUP) ----------------
            'jumlah_sara_hidup' => ['page' => 2, 'section' => 'A. SARA HIDUP', 'label' => 'JUM. Yang Diperlukan', 'zone' => [520, 295, 400, 55], 'type' => 'amount'],
            'no_akaun_bimb' => ['page' => 2, 'section' => 'A. SARA HIDUP', 'label' => 'No. Akaun BIMB', 'zone' => [1000, 295, 800, 55], 'type' => 'text'],
            'bantuan_agensi_lain_nyatakan' => ['page' => 2, 'section' => 'A. SARA HIDUP', 'label' => 'Bantuan dari Agensi lain (Nyatakan)', 'zone' => [700, 360, 700, 50], 'type' => 'text'],
            'bantuan_agensi_lain_jumlah' => ['page' => 2, 'section' => 'A. SARA HIDUP', 'label' => 'Bantuan dari Agensi lain (RM)', 'zone' => [1450, 360, 350, 50], 'type' => 'amount'],

            // ---------------- PAGE 2 : 5B PERUBATAN ----------------
            'perubatan_nama_tanggungan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Nama Tanggungan', 'zone' => [500, 640, 1300, 55], 'type' => 'text'],
            'perubatan_hubungan_tanggungan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Hubungan', 'zone' => [500, 705, 1300, 55], 'type' => 'text'],
            'perubatan_no_kp_tanggungan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'No. Kad Pengenalan Tanggungan', 'zone' => [500, 772, 1300, 55], 'type' => 'ic'],
            'perubatan_jenis_penyakit' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Jenis Penyakit', 'zone' => [500, 834, 1300, 55], 'type' => 'text'],
            'perubatan_mula_mengidap' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Mula Mengidap', 'zone' => [500, 899, 1300, 55], 'type' => 'date'],
            'perubatan_tempat_rawatan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Tempat Rawatan', 'zone' => [400, 967, 700, 55], 'type' => 'text'],
            'perubatan_kos_rawatan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Kos Rawatan', 'zone' => [1200, 967, 600, 55], 'type' => 'amount'],
            'perubatan_peralatan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Peralatan Diperlukan', 'zone' => [400, 1031, 700, 55], 'type' => 'text'],
            'perubatan_harga_alatan' => ['page' => 2, 'section' => 'B. PERUBATAN', 'label' => 'Harga Alatan', 'zone' => [1200, 1031, 600, 55], 'type' => 'amount'],

            // ---------------- PAGE 2 : 5C BERDIKARI ----------------
            'berdikari_jenis_peralatan' => ['page' => 2, 'section' => 'C. BERDIKARI', 'label' => 'Jenis peralatan yang dipohon', 'zone' => [600, 1174, 1200, 55], 'type' => 'text'],
            'berdikari_harga_peralatan' => ['page' => 2, 'section' => 'C. BERDIKARI', 'label' => 'Harga Peralatan/Modal', 'zone' => [700, 1240, 1100, 55], 'type' => 'amount'],
            'berdikari_jenis_perniagaan' => ['page' => 2, 'section' => 'C. BERDIKARI', 'label' => 'Jenis Perniagaan', 'zone' => [600, 1304, 1200, 55], 'type' => 'text'],
            'berdikari_sebab_memerlukan' => ['page' => 2, 'section' => 'C. BERDIKARI', 'label' => 'Sebab memerlukan', 'zone' => [600, 1370, 1200, 55], 'type' => 'text'],
            'berdikari_pengalaman_tahun' => ['page' => 2, 'section' => 'C. BERDIKARI', 'label' => 'Pengalaman Berniaga (tahun)', 'zone' => [700, 1440, 350, 45], 'type' => 'number'],

            // ---------------- PAGE 2 : 5D PERUMAHAN ----------------
            'nama_dalam_geran' => ['page' => 2, 'section' => 'D. PERUMAHAN', 'label' => 'Nama dalam Geran', 'zone' => [600, 1647, 1200, 55], 'type' => 'text'],
            'no_geran' => ['page' => 2, 'section' => 'D. PERUMAHAN', 'label' => 'No. Geran', 'zone' => [600, 1714, 1200, 55], 'type' => 'text'],

            // ---------------- PAGE 2 : 5E MENJELASKAN HUTANG ----------------
            'sebab_berhutang' => ['page' => 2, 'section' => 'E. MENJELASKAN HUTANG', 'label' => 'Sebab berhutang', 'zone' => [500, 1920, 1300, 55], 'type' => 'text'],
            'jumlah_hutang' => ['page' => 2, 'section' => 'E. MENJELASKAN HUTANG', 'label' => 'Jumlah Hutang', 'zone' => [500, 1985, 1300, 55], 'type' => 'amount'],
            'sebab_gagal_bayar_hutang' => ['page' => 2, 'section' => 'E. MENJELASKAN HUTANG', 'label' => 'Sebab gagal bayar hutang', 'zone' => [500, 2051, 1300, 55], 'type' => 'text'],
            'nama_pemberi_hutang' => ['page' => 2, 'section' => 'E. MENJELASKAN HUTANG', 'label' => 'Nama Pemberi Hutang', 'zone' => [500, 2119, 1300, 55], 'type' => 'text'],
            'no_kp_pemberi_hutang' => ['page' => 2, 'section' => 'E. MENJELASKAN HUTANG', 'label' => 'No. K/P Pemberi Hutang', 'zone' => [500, 2184, 1300, 55], 'type' => 'ic'],

            // ---------------- PAGE 3 : MAKLUMAT TEMPAT TINGGAL ----------------
            'nilai_tempat_tinggal' => ['page' => 3, 'section' => 'MAKLUMAT TEMPAT TINGGAL', 'label' => 'Menyewa (RM)', 'zone' => [900, 700, 700, 50], 'type' => 'amount'],
            'lain_lain_nyatakan_tempat_tinggal' => ['page' => 3, 'section' => 'MAKLUMAT TEMPAT TINGGAL', 'label' => 'Lain-lain Nyatakan', 'zone' => [600, 780, 1100, 55], 'type' => 'text'],

            // ---------------- PAGE 3 : MAKLUMAT HARTA ----------------
            'harta_unit_rumah' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Rumah (Unit)', 'zone' => [330, 960, 140, 45], 'type' => 'number'],
            'harta_nilai_rumah' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Rumah (Nilai RM)', 'zone' => [560, 960, 280, 45], 'type' => 'amount'],
            'harta_ekar' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Tanah (Ekar)', 'zone' => [1040, 960, 110, 45], 'type' => 'number'],
            'harta_nilai_tanah' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Tanah (Nilai RM)', 'zone' => [1150, 960, 280, 45], 'type' => 'amount'],
            'harta_unit_kereta' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Kereta (Unit)', 'zone' => [330, 1030, 140, 45], 'type' => 'number'],
            'harta_nilai_kereta' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Kereta (Nilai RM)', 'zone' => [560, 1030, 280, 45], 'type' => 'amount'],
            'harta_unit_motosikal' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Motosikal (Unit)', 'zone' => [1040, 1020, 110, 45], 'type' => 'number'],
            'harta_nilai_motosikal' => ['page' => 3, 'section' => 'MAKLUMAT HARTA', 'label' => 'Motosikal (Nilai RM)', 'zone' => [1150, 1020, 280, 45], 'type' => 'amount'],

            // ---------------- PAGE 4 : PENGAKUAN & BUTIRAN PENGAKUAN ----------------
            'pengakuan_nama' => ['page' => 4, 'section' => 'PENGAKUAN PEMOHON', 'label' => 'Nama Pengakuan', 'zone' => [600, 205, 1200, 45], 'type' => 'text'],
            'pengakuan_tarikh' => ['page' => 4, 'section' => 'PENGAKUAN PEMOHON', 'label' => 'Tarikh', 'zone' => [240, 450, 700, 55], 'type' => 'date'],
            'pengesah_nama' => ['page' => 4, 'section' => 'BUTIRAN PENGAKUAN', 'label' => 'Nama Pengesah', 'zone' => [250, 770, 900, 55], 'type' => 'text'],
            'pengesah_jawatan' => ['page' => 4, 'section' => 'BUTIRAN PENGAKUAN', 'label' => 'Jawatan', 'zone' => [250, 835, 900, 45], 'type' => 'text'],
            'pengesah_ulasan' => ['page' => 4, 'section' => 'BUTIRAN PENGAKUAN', 'label' => 'Ulasan', 'zone' => [250, 885, 900, 45], 'type' => 'text'],
            'pengesah_tarikh' => ['page' => 4, 'section' => 'BUTIRAN PENGAKUAN', 'label' => 'Tarikh Pengesah', 'zone' => [1100, 985, 500, 40], 'type' => 'date'],
        ];
    }

    /**
     * Checkbox groups for pixel-density detection.
     * options => key => [label, yTop, yBottom] (y band constrains the label search;
     * the box is probed immediately to the LEFT of the option label word).
     *
     * @return array<string, array{page:int, options:array<string, array{0:string,1:int,2:int}>}>
     */
    public static function checkboxGroups(): array
    {
        return [
            'kesihatan' => [
                'page' => 1,
                'options' => [
                    'sihat' => ['Sihat', 2075, 2140],
                    'sakit' => ['Sakit', 2075, 2140],
                    'cacat' => ['Cacat', 2075, 2140],
                ],
            ],
            'status_perkahwinan' => [
                'page' => 1,
                'options' => [
                    'bujang' => ['Bujang', 2145, 2210],
                    'berkahwin' => ['Berkahwin', 2145, 2210],
                    'janda' => ['Janda', 2145, 2210],
                    'duda' => ['Duda', 2145, 2210],
                ],
            ],
            'merokok' => [
                'page' => 1,
                'options' => [
                    'ya' => ['Ya', 2225, 2280],
                    'tidak' => ['Tidak', 2225, 2280],
                ],
            ],
            // Section 3 bantuan table is handled via filled amounts (the rows
            // are ticked only by writing the amount on this form), so the
            // bantuan checkboxes are not probed here.
            // Page 1 — Perumahan sub-options (Bina Baru / Baik Pulih)
            'perumahan' => [
                'page' => 1,
                'options' => [
                    'bina_baru' => ['Bina baru', 1300, 1365],
                    'baik_pulih' => ['Baik Pulih', 1365, 1415],
                ],
            ],
            // Page 2
            'perubatan_pemohon_or_tanggungan' => [
                'page' => 2,
                'options' => [
                    'pemohon' => ['Pemohon', 550, 610],
                    'tanggungan' => ['Tanggungan', 550, 610],
                ],
            ],
            'perubatan_jenis_rawatan' => [
                'page' => 2,
                'options' => [
                    'dialisis' => ['Dialisis', 490, 550],
                    'pelbagai' => ['Pelbagai', 490, 550],
                ],
            ],
            'jenis_berdikari' => [
                'page' => 2,
                'options' => [
                    'peralatan' => ['Peralatan', 1140, 1185],
                    'modal' => ['Modal', 1140, 1185],
                ],
            ],
            'berdikari_pengalaman_ada' => [
                'page' => 2,
                'options' => [
                    'ada' => ['Ada', 1435, 1480],
                    'tiada' => ['Tiada', 1435, 1480],
                ],
            ],
            'status_tanah' => [
                'page' => 2,
                'options' => [
                    'sendiri' => ['Sendiri', 1580, 1630],
                    'sewa' => ['Sewa', 1580, 1630],
                    'tumpang' => ['Tumpang', 1580, 1630],
                ],
            ],
            'perumahan_laporan_polis' => [
                'page' => 2,
                'options' => [
                    'ada' => ['Ada', 1778, 1825],
                    'tiada' => ['Tiada', 1778, 1825],
                ],
            ],
            // Page 3
            'tempat_tinggal' => [
                'page' => 3,
                'options' => [
                    'sendiri' => ['Sendiri', 700, 760],
                    'menumpang' => ['Menumpang', 700, 760],
                    'menyewa' => ['Menyewe', 700, 760],
                    'lain_lain' => ['Lain-lain', 780, 840],
                ],
            ],
            'harta_rumah' => ['page' => 3, 'options' => ['ada' => ['Rumah', 945, 1005]]],
            'harta_tanah' => ['page' => 3, 'options' => ['ada' => ['Tanah', 945, 1005]]],
            'harta_kereta' => ['page' => 3, 'options' => ['ada' => ['Kereta', 1015, 1075]]],
            'harta_motosikal' => ['page' => 3, 'options' => ['ada' => ['Motosikal', 1015, 1075]]],
        ];
    }

    /**
     * Fixed checkbox box zones (reference space) for groups where the box is not
     * reliably anchorable to a label word (e.g. the bantuan table where the row
     * checkbox sits at the far left margin). Keyed by checkbox field key.
     *
     * @return array<string, array{x:int,y:int,w:int,h:int,page:int}>
     */
    public static function fixedCheckboxZones(): array
    {
        return [
            'perumahan.bina_baru' => ['x' => 330, 'y' => 1315, 'w' => 75, 'h' => 50, 'page' => 1],
            'perumahan.baik_pulih' => ['x' => 400, 'y' => 1375, 'w' => 75, 'h' => 50, 'page' => 1],
        ];
    }

    /**
     * Ink zones whose dark-pixel ratio indicates presence of a signature/stamp.
     * baseline is the measured "empty" density on the blank template (prefers
     * the calibration JSON); a zone is considered signed/stamped when its
     * density clearly exceeds it.
     *
     * @return array<string, array{page:int, zone:array{x:int,y:int,w:int,h:int}, baseline:float}>
     */
    public static function inkZones(): array
    {
        $zones = [
            'signature_pemohon' => ['page' => 4, 'zone' => ['x' => 600, 'y' => 470, 'w' => 1100, 'h' => 75], 'baseline' => 0.0229],
            'cop_pengesah' => ['page' => 4, 'zone' => ['x' => 1300, 'y' => 880, 'w' => 500, 'h' => 100], 'baseline' => 0.0269],
        ];

        $calibrated = static::calibration()['ink_zones'] ?? [];

        foreach ($zones as $key => &$zone) {
            if (isset($calibrated[$key]) && $calibrated[$key] !== null) {
                $zone['baseline'] = (float) $calibrated[$key];
            }
        }

        return $zones;
    }

    /**
     * Load the calibration JSON (measured from the blank template) once per
     * request. Returns an empty array when the file is absent or invalid.
     *
     * @return array<string, mixed>
     */
    public static function calibration(): array
    {
        if (static::$calibrationCache !== null) {
            return static::$calibrationCache;
        }

        static::$calibrationCache = [];

        $path = (string) config('ocr.document_intelligence.calibration_path');
        if ($path !== '' && file_exists($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                static::$calibrationCache = $decoded;
            }
        }

        return static::$calibrationCache;
    }

    /**
     * Calibrated "empty" ink density per checkbox box, measured on the blank
     * template scan. Keyed by `group.option`. Prefers the calibration JSON
     * produced by `php artisan ocr:calibrate-template`; falls back to the
     * hardcoded defaults (measured from the blank template) when the file is
     * absent. A null baseline means the group statistic (median) is used.
     *
     * @return array<string, float>
     */
    public static function checkboxBaselines(): array
    {
        $defaults = [
            // Page 1 — Kesihatan
            'kesihatan.sihat' => 0.0044,
            'kesihatan.sakit' => 0.0147,
            'kesihatan.cacat' => 0.0228,
            // Page 1 — Status Perkahwinan
            'status_perkahwinan.bujang' => 0.0022,
            'status_perkahwinan.berkahwin' => 0.0037,
            'status_perkahwinan.janda' => 0.0243,
            'status_perkahwinan.duda' => 0.0801,
            // Page 1 — Merokok
            'merokok.ya' => 0.0015,
            'merokok.tidak' => 0.0044,
            // Page 1 — Perumahan sub-options (Bina Baru / Baik Pulih)
            'perumahan.bina_baru' => 0.0120,
            'perumahan.baik_pulih' => 0.0120,
            // Page 3 — Harta
            'harta_rumah.ada' => 0.0029,
            'harta_tanah.ada' => 0.0022,
            'harta_kereta.ada' => 0.0037,
            'harta_motosikal.ada' => 0.0030,
        ];

        $calibrated = static::calibration()['checkbox_baselines'] ?? [];

        return array_merge($defaults, array_filter($calibrated, fn ($v) => $v !== null));
    }

    /**
     * Section 3 bantuan rows: amount + reason share a value zone on each row.
     * The first numeric token is the requested amount, the rest is the reason.
     *
     * @return array<int, array{key:string, zone:array<int, int>}>
     */
    public static function bantuanRows(): array
    {
        return [
            ['key' => 'sara_hidup', 'zone' => [350, 1135, 620, 45]],
            ['key' => 'perubatan', 'zone' => [350, 1205, 620, 45]],
            ['key' => 'berdikari', 'zone' => [350, 1275, 620, 45]],
            ['key' => 'perumahan', 'zone' => [400, 1425, 500, 45]],
            ['key' => 'hutang', 'zone' => [350, 1490, 620, 45]],
        ];
    }

    /**
     * Dependents / working heirs table (page 3, section 6). Column x ranges and
     * the vertical band that contains all 6 rows. Row segmentation is derived
     * by clustering words by vertical position.
     */
    public static function dependentsTable(): array
    {
        return [
            'page' => 3,
            'y_top' => 250,
            'y_bottom' => 680,
            'columns' => [
                'nama' => ['x' => 185, 'w' => 520],
                'no_ic' => ['x' => 705, 'w' => 200],
                'sekolah_pekerjaan' => ['x' => 905, 'w' => 180],
                'pendapatan' => ['x' => 1085, 'w' => 150],
                'hubungan' => ['x' => 1235, 'w' => 170],
                'tinggal_bersama' => ['x' => 1405, 'w' => 160],
            ],
        ];
    }

    /**
     * Employment table (page 3, section 9): two rows (Pemohon / Pasangan) with
     * fixed column ranges.
     */
    public static function employmentTable(): array
    {
        return [
            'page' => 3,
            'rows' => [
                'pemohon' => ['label' => 'Pemohon', 'y' => 1195, 'h' => 80],
                'pasangan' => ['label' => 'Pasangan', 'y' => 1420, 'h' => 80],
            ],
            'columns' => [
                'nama' => ['x' => 330, 'w' => 400],
                'no_kp' => ['x' => 750, 'w' => 200],
                'majikan' => ['x' => 980, 'w' => 200],
                'pekerjaan' => ['x' => 1200, 'w' => 220],
                'pendapatan' => ['x' => 1440, 'w' => 250],
            ],
        ];
    }

    /**
     * Income rows (page 3, section 10, left label column). Each row has a label
     * keyword, a y band, and the amount column zone (income amounts sit between
     * the income label and the expense column). `jumlah_pendapatan` is the table
     * total row.
     */
    public static function incomeRows(): array
    {
        return [
            ['key' => 'diri', 'label' => 'Diri', 'y_top' => 1630, 'y_bottom' => 1690, 'amount_zone' => [560, 1630, 350, 60]],
            ['key' => 'pasangan_ibubapa_penjaga', 'label' => 'Pasangan', 'y_top' => 1695, 'y_bottom' => 1755, 'amount_zone' => [560, 1695, 350, 60]],
            ['key' => 'pencen_perkeso', 'label' => 'Pencen', 'y_top' => 1760, 'y_bottom' => 1820, 'amount_zone' => [560, 1760, 350, 60]],
            ['key' => 'sumbangan_anak', 'label' => 'Sumbangan', 'y_top' => 1825, 'y_bottom' => 1890, 'amount_zone' => [560, 1825, 350, 65]],
            ['key' => 'bantuan_agensi', 'label' => 'Bantuan dari agensi lain', 'y_top' => 1980, 'y_bottom' => 2045, 'amount_zone' => [560, 1980, 350, 65]],
            ['key' => 'lain_lain', 'label' => 'Lain-lain pendapatan', 'y_top' => 2045, 'y_bottom' => 2100, 'amount_zone' => [560, 2045, 350, 55]],
            ['key' => 'jumlah_pendapatan', 'label' => 'JUMLAH PENDAPATAN', 'y_top' => 2100, 'y_bottom' => 2190, 'amount_zone' => [560, 2100, 350, 90]],
        ];
    }

    /**
     * Expenses rows (page 3, section 10, right label column). Amounts sit in the
     * far-right column. `jumlah_perbelanjaan` is the table total row.
     */
    public static function expenseRows(): array
    {
        return [
            ['key' => 'makan_minum', 'label' => 'Makan', 'y_top' => 1630, 'y_bottom' => 1690, 'amount_zone' => [1300, 1630, 540, 60]],
            ['key' => 'bil_api_air', 'label' => 'Bil Api', 'y_top' => 1695, 'y_bottom' => 1755, 'amount_zone' => [1300, 1695, 540, 60]],
            ['key' => 'persekolahan', 'label' => 'Persekolahan', 'y_top' => 1760, 'y_bottom' => 1820, 'amount_zone' => [1300, 1760, 540, 60]],
            ['key' => 'pengangkutan', 'label' => 'Pengangkutan', 'y_top' => 1825, 'y_bottom' => 1890, 'amount_zone' => [1300, 1825, 540, 65]],
            ['key' => 'perubatan', 'label' => 'Perubatan', 'y_top' => 1890, 'y_bottom' => 1950, 'amount_zone' => [1300, 1890, 540, 60]],
            ['key' => 'sewa_ansuran', 'label' => 'Sewa', 'y_top' => 1950, 'y_bottom' => 2010, 'amount_zone' => [1300, 1950, 540, 60]],
            ['key' => 'lain_lain', 'label' => 'Lain-lain', 'y_top' => 2010, 'y_bottom' => 2070, 'amount_zone' => [1300, 2010, 540, 60]],
            ['key' => 'jumlah_perbelanjaan', 'label' => 'JUMLAH PERBELANJAAN', 'y_top' => 2100, 'y_bottom' => 2190, 'amount_zone' => [1300, 2100, 540, 90]],
        ];
    }

    /**
     * Section structure for the "detect form structure" requirement.
     *
     * @return array<int, array{page:int, number:string, title:string}>
     */
    public static function sections(): array
    {
        return [
            ['page' => 1, 'number' => '1', 'title' => 'SYARAT UMUM PERMOHONAN BANTUAN'],
            ['page' => 1, 'number' => '2', 'title' => 'DOKUMEN LAMPIRAN UTAMA'],
            ['page' => 1, 'number' => '3', 'title' => 'MAKLUMAT PERMOHONAN BANTUAN'],
            ['page' => 1, 'number' => '4', 'title' => 'MAKLUMAT PENGENALAN PEMOHON'],
            ['page' => 2, 'number' => '5', 'title' => 'MAKLUMAT TAMBAHAN PERMOHONAN BANTUAN'],
            ['page' => 2, 'number' => '5A', 'title' => 'A. SARA HIDUP'],
            ['page' => 2, 'number' => '5B', 'title' => 'B. PERUBATAN'],
            ['page' => 2, 'number' => '5C', 'title' => 'C. BERDIKARI'],
            ['page' => 2, 'number' => '5D', 'title' => 'D. PERUMAHAN'],
            ['page' => 2, 'number' => '5E', 'title' => 'E. MENJELASKAN HUTANG'],
            ['page' => 3, 'number' => '6', 'title' => 'MAKLUMAT TANGGUNGAN ATAU WARIS YANG TELAH BEKERJA'],
            ['page' => 3, 'number' => '7', 'title' => 'MAKLUMAT TEMPAT TINGGAL'],
            ['page' => 3, 'number' => '8', 'title' => 'MAKLUMAT HARTA'],
            ['page' => 3, 'number' => '9', 'title' => 'MAKLUMAT PEKERJAAN PEMOHON/PASANGAN'],
            ['page' => 3, 'number' => '10', 'title' => 'MAKLUMAT PENDAPATAN & PERBELANJAAN BULANAN'],
            ['page' => 4, 'number' => '11', 'title' => 'PENGAKUAN PEMOHON'],
            ['page' => 4, 'number' => '12', 'title' => 'BUTIRAN PENGAKUAN'],
        ];
    }

    /**
     * Type-aware matcher specs for pages that rasterise blank but have a text
     * layer (e.g. vector AcroForm PDFs). Consumed by
     * {@see TextLayerSequenceParser} in visual reading order — each matcher
     * consumes the tokens its type describes (multi-token names, grouped
     * account numbers, dates, ICs). `max` bounds free-text fields.
     *
     * @return array<int, array<int, array{key:string, type:string, max?:int}>>
     */
    public static function textLayerSpecs(): array
    {
        return [
            2 => [
                ['key' => 'jumlah_sara_hidup', 'type' => 'amount'],
                ['key' => 'no_akaun_bimb', 'type' => 'account'],
                ['key' => 'bantuan_agensi_lain_nyatakan', 'type' => 'account', 'max' => 1],
                ['key' => 'bantuan_agensi_lain_jumlah', 'type' => 'amount'],
                ['key' => 'perubatan_nama_tanggungan', 'type' => 'name'],
                ['key' => 'perubatan_hubungan_tanggungan', 'type' => 'relationship'],
                ['key' => 'perubatan_no_kp_tanggungan', 'type' => 'ic'],
                ['key' => 'perubatan_jenis_penyakit', 'type' => 'text', 'max' => 3],
                ['key' => 'perubatan_mula_mengidap', 'type' => 'date'],
                ['key' => 'perubatan_tempat_rawatan', 'type' => 'text', 'max' => 4],
                ['key' => 'perubatan_kos_rawatan', 'type' => 'amount'],
                ['key' => 'perubatan_harga_alatan', 'type' => 'amount'],
                ['key' => 'nama_dalam_geran', 'type' => 'name'],
                ['key' => 'no_geran', 'type' => 'code'],
            ],
        ];
    }
}
