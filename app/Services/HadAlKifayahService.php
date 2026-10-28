<?php

namespace App\Services;

use App\Models\HadKifayahSetting;
use App\Models\ProfilAsnaf;
use Illuminate\Support\Facades\Cache;

class HadAlKifayahService
{
    public const CACHE_KEY = 'had_kifayah_settings:active';

    public const CACHE_TTL = 300;

    public const STATUS_LAYAK = 'Layak';

    public const STATUS_TIDAK_LAYAK = 'Tidak Layak';

    public const STATUS_DALAM_SEMAKAN = 'Dalam Semakan';

    /**
     * All active settings as a flat list of plain arrays.
     * Cached for 5 minutes. Returns arrays (not Eloquent models) to avoid
     * serialization issues with the database cache driver.
     *
     * @return array<int, array{kategori:string, subkategori:?string, nilai:float}>
     */
    public static function active(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return HadKifayahSetting::active()
                ->get(['kategori', 'subkategori', 'nilai'])
                ->map(fn ($s) => [
                    'kategori' => (string) $s->kategori,
                    'subkategori' => $s->subkategori !== null ? (string) $s->subkategori : null,
                    'nilai' => (float) $s->nilai,
                ])
                ->all();
        });
    }

    /**
     * Bust the cache (call this from the settings admin save action).
     */
    public static function bustCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Look up a single rate.
     */
    public static function getRate(string $kategori, ?string $subkategori = null): float
    {
        foreach (self::active() as $row) {
            if ($row['kategori'] === $kategori && $row['subkategori'] === $subkategori) {
                return $row['nilai'];
            }
        }

        return 0.0;
    }

    /**
     * Build a key-value map for the calculator view.
     *
     * Shape:
     *   [
     *     'Ketua Keluarga' => 1316.0,
     *     'Dewasa' => [
     *        'Isteri / Suami'                 => 162.0,
     *        'Ibu Bapa Tanggungan'            => 156.0,
     *        ...
     *     ],
     *     ...
     *   ]
     */
    public static function ratesMap(): array
    {
        $map = [];
        foreach (self::active() as $row) {
            if ($row['subkategori'] === null) {
                $map[$row['kategori']] = $row['nilai'];
            } else {
                $map[$row['kategori']][$row['subkategori']] = $row['nilai'];
            }
        }

        return $map;
    }

    /**
     * Run the full MAIDAM-style calculation.
     *
     * Expected input keys (all optional, default 0):
     *   pendapatan_pemohon, pendapatan_pasangan, pendapatan_anak, pendapatan_lain
     *   n_isteri_suami, n_ibu_bapa, n_dewasa_tidak_bekerja, n_dewasa_bekerja_tanggung,
     *   n_ipt, n_remaja, n_kanak_rendah, n_kanak_tadika
     *   perumahan ('sendiri'|'sewa')
     *   utiliti_input, pengangkutan_input, perubatan_input
     *
     * @return array{pendapatan_total:float, had_kifayah_total:float, baki:float, status:string, confidence:int, recommendation:string, reasoning:array<int,string>, breakdown:array<int,array{label:string,nilai:float,subtotal:float}>}
     */
    public static function calculate(array $input): array
    {
        $pendapatanPemohon = (float) ($input['pendapatan_pemohon'] ?? 0);
        $pendapatanPasangan = (float) ($input['pendapatan_pasangan'] ?? 0);
        $pendapatanAnak = (float) ($input['pendapatan_anak'] ?? 0);
        $pendapatanLain = (float) ($input['pendapatan_lain'] ?? 0);

        $pendapatanTotal = $pendapatanPemohon + $pendapatanPasangan + $pendapatanAnak + $pendapatanLain;

        $nIs = (int) ($input['n_isteri_suami'] ?? 0);
        $nIbuBapa = (int) ($input['n_ibu_bapa'] ?? 0);
        $nDewasaTk = (int) ($input['n_dewasa_tidak_bekerja'] ?? 0);
        $nDewasaBk = (int) ($input['n_dewasa_bekerja_tanggung'] ?? 0);
        $nIpt = (int) ($input['n_ipt'] ?? 0);
        $nRemaja = (int) ($input['n_remaja'] ?? 0);
        $nKanakRendah = (int) ($input['n_kanak_rendah'] ?? 0);
        $nKanakTadika = (int) ($input['n_kanak_tadika'] ?? 0);

        $perumahan = $input['perumahan'] ?? 'sendiri';
        $utilitiIn = (float) ($input['utiliti_input'] ?? 0);
        $pengangkutanIn = (float) ($input['pengangkutan_input'] ?? 0);
        $perubatanIn = (float) ($input['perubatan_input'] ?? 0);

        $rKetua = self::getRate('Ketua Keluarga');
        $rIs = self::getRate('Dewasa', 'Isteri / Suami');
        $rIbuBapa = self::getRate('Dewasa', 'Ibu Bapa Tanggungan');
        $rDewasaTk = self::getRate('Dewasa', 'Dewasa Tidak Bekerja');
        $rDewasaBk = self::getRate('Dewasa', 'Dewasa Bekerja Tetapi Masih Ditanggung');
        $rIpt = self::getRate('IPT');
        $rRemaja = self::getRate('Remaja');
        $rKanakRendah = self::getRate('Kanak-Kanak', 'Sekolah Rendah');
        $rKanakTadika = self::getRate('Kanak-Kanak', 'Tadika');
        $rRumahSewa = self::getRate('Rumah Sewa');

        $subtotalKetua = $rKetua;
        $subtotalIs = $nIs * $rIs;
        $subtotalIbuBapa = $nIbuBapa * $rIbuBapa;
        $subtotalDewasaTk = $nDewasaTk * $rDewasaTk;
        $subtotalDewasaBk = $nDewasaBk * $rDewasaBk;
        $subtotalIpt = $nIpt * $rIpt;
        $subtotalRemaja = $nRemaja * $rRemaja;
        $subtotalKanakRendah = $nKanakRendah * $rKanakRendah;
        $subtotalKanakTadika = $nKanakTadika * $rKanakTadika;
        $subtotalRumahSewa = ($perumahan === 'sewa') ? $rRumahSewa : 0.0;
        $subtotalUtiliti = $utilitiIn;
        $subtotalPengangkutan = $pengangkutanIn;
        $subtotalPerubatan = $perubatanIn;

        $hadKifayahTotal = $subtotalKetua + $subtotalIs + $subtotalIbuBapa + $subtotalDewasaTk
            + $subtotalDewasaBk + $subtotalIpt + $subtotalRemaja + $subtotalKanakRendah
            + $subtotalKanakTadika + $subtotalRumahSewa + $subtotalUtiliti
            + $subtotalPengangkutan + $subtotalPerubatan;

        $baki = $pendapatanTotal - $hadKifayahTotal;
        $status = ($baki < 0) ? self::STATUS_LAYAK : self::STATUS_TIDAK_LAYAK;

        $gapPct = ($hadKifayahTotal > 0) ? (abs($baki) / $hadKifayahTotal) * 100 : 0.0;
        $confidence = (int) max(50, min(99, round(50 + $gapPct * 0.5)));

        $recommendation = ($status === self::STATUS_LAYAK)
            ? __('service.hadkifayah.recommendation.eligible')
            : __('service.hadkifayah.recommendation.not_eligible');

        $totalTanggungan = $nIs + $nIbuBapa + $nDewasaTk + $nDewasaBk + $nIpt + $nRemaja + $nKanakRendah + $nKanakTadika;
        $totalPerbelanjaan = $utilitiIn + $pengangkutanIn + $perubatanIn;

        $reasoning = [];
        $reasoning[] = ($baki < 0)
            ? __('service.hadkifayah.reasoning.income_below', [
                'income' => number_format($pendapatanTotal, 2),
                'threshold' => number_format($hadKifayahTotal, 2),
            ])
            : __('service.hadkifayah.reasoning.income_not_below', [
                'income' => number_format($pendapatanTotal, 2),
                'threshold' => number_format($hadKifayahTotal, 2),
            ]);
        $reasoning[] = ($totalTanggungan >= 3)
            ? __('service.hadkifayah.reasoning.dependants_high', ['count' => $totalTanggungan])
            : __('service.hadkifayah.reasoning.dependants_low', ['count' => $totalTanggungan]);
        if ($baki < 0) {
            $gapStr = number_format($gapPct, 1).'%';
            $reasoning[] = ($gapPct >= 30)
                ? __('service.hadkifayah.reasoning.gap_large', ['gap' => $gapStr])
                : __('service.hadkifayah.reasoning.gap_small', ['gap' => $gapStr]);
        }
        $reasoning[] = ($totalPerbelanjaan > 0)
            ? __('service.hadkifayah.reasoning.expenses_recorded', ['amount' => number_format($totalPerbelanjaan, 2)])
            : __('service.hadkifayah.reasoning.expenses_none');

        $breakdown = [
            ['label' => __('service.hadkifayah.breakdown.head'),                                                    'nilai' => $rKetua,        'subtotal' => $subtotalKetua],
            ['label' => __('service.hadkifayah.breakdown.spouse', ['count' => $nIs]),                                'nilai' => $rIs,           'subtotal' => $subtotalIs],
            ['label' => __('service.hadkifayah.breakdown.parents', ['count' => $nIbuBapa]),                          'nilai' => $rIbuBapa,      'subtotal' => $subtotalIbuBapa],
            ['label' => __('service.hadkifayah.breakdown.adult_unemployed', ['count' => $nDewasaTk]),               'nilai' => $rDewasaTk,     'subtotal' => $subtotalDewasaTk],
            ['label' => __('service.hadkifayah.breakdown.adult_working_dependant', ['count' => $nDewasaBk]),         'nilai' => $rDewasaBk,     'subtotal' => $subtotalDewasaBk],
            ['label' => __('service.hadkifayah.breakdown.higher_education', ['count' => $nIpt]),                     'nilai' => $rIpt,          'subtotal' => $subtotalIpt],
            ['label' => __('service.hadkifayah.breakdown.teenager', ['count' => $nRemaja]),                          'nilai' => $rRemaja,       'subtotal' => $subtotalRemaja],
            ['label' => __('service.hadkifayah.breakdown.primary_school', ['count' => $nKanakRendah]),               'nilai' => $rKanakRendah,  'subtotal' => $subtotalKanakRendah],
            ['label' => __('service.hadkifayah.breakdown.kindergarten', ['count' => $nKanakTadika]),                 'nilai' => $rKanakTadika,  'subtotal' => $subtotalKanakTadika],
            ['label' => ($perumahan === 'sewa') ? __('service.hadkifayah.breakdown.rented_selected') : __('service.hadkifayah.breakdown.rented_not_selected'), 'nilai' => $rRumahSewa, 'subtotal' => $subtotalRumahSewa],
            ['label' => __('service.hadkifayah.breakdown.utilities'),                                                'nilai' => $utilitiIn,     'subtotal' => $subtotalUtiliti],
            ['label' => __('service.hadkifayah.breakdown.transport'),                                                'nilai' => $pengangkutanIn, 'subtotal' => $subtotalPengangkutan],
            ['label' => __('service.hadkifayah.breakdown.medical'),                                                  'nilai' => $perubatanIn,   'subtotal' => $subtotalPerubatan],
        ];

        return [
            'pendapatan_total' => round($pendapatanTotal, 2),
            'had_kifayah_total' => round($hadKifayahTotal, 2),
            'baki' => round($baki, 2),
            'status' => $status,
            'confidence' => $confidence,
            'recommendation' => $recommendation,
            'reasoning' => $reasoning,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Apply the calculation to a ProfilAsnaf model and mutate it in place.
     */
    public static function apply(ProfilAsnaf $p): ProfilAsnaf
    {
        $r = self::calculate([
            'pendapatan_pemohon' => (float) $p->pendapatan,
            'pendapatan_pasangan' => 0,
            'pendapatan_anak' => 0,
            'pendapatan_lain' => 0,
            'n_isteri_suami' => (int) $p->n_isteri_suami,
            'n_ibu_bapa' => (int) $p->n_ibu_bapa,
            'n_dewasa_tidak_bekerja' => (int) $p->n_dewasa_tidak_bekerja,
            'n_dewasa_bekerja_tanggung' => (int) $p->n_dewasa_bekerja_tanggung,
            'n_ipt' => (int) $p->n_ipt,
            'n_remaja' => (int) $p->n_remaja,
            'n_kanak_rendah' => (int) $p->n_kanak_rendah,
            'n_kanak_tadika' => (int) $p->n_kanak_tadika,
            'perumahan' => $p->perumahan ?? 'sendiri',
            'utiliti_input' => (float) $p->utiliti,
            'pengangkutan_input' => (float) $p->pengangkutan_input,
            'perubatan_input' => (float) $p->perubatan_input,
        ]);

        $p->had_kifayah = $r['had_kifayah_total'];
        $p->status_kelayakan = $r['status'];
        $p->ai_score = $r['confidence'];

        return $p;
    }
}
