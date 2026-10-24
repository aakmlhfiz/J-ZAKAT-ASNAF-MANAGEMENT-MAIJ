<?php

namespace App\Services;

use App\Models\ProfilAsnaf;

class EligibilityService
{
    public const ASAS = 1200.0;

    public const PER_TANGGUNGAN = 350.0;

    /**
     * Legacy signature — preserved for any external caller (e.g. chat tool).
     * Delegates to the MAIDAM-style engine. Tanggungan is approximated
     * as `n_remaja`; the other per-kategori counts are 0.
     *
     * @return array{had_kifayah: float, status_kelayakan: string, ai_score: float}
     */
    public static function compute(float $pendapatan, int $tanggungan, float $sewa, float $utiliti): array
    {
        $r = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => $pendapatan,
            'pendapatan_pasangan' => 0,
            'pendapatan_anak' => 0,
            'pendapatan_lain' => 0,
            'n_isteri_suami' => 0,
            'n_ibu_bapa' => 0,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 0,
            'n_remaja' => $tanggungan,
            'n_kanak_rendah' => 0,
            'n_kanak_tadika' => 0,
            'perumahan' => $sewa > 0 ? 'sewa' : 'sendiri',
            'utiliti_input' => $utiliti,
            'pengangkutan_input' => 0,
            'perubatan_input' => 0,
        ]);

        return [
            'had_kifayah' => $r['had_kifayah_total'],
            'status_kelayakan' => $r['status'],
            'ai_score' => $r['confidence'],
        ];
    }

    /**
     * Apply the calculation to a ProfilAsnaf model. Delegates to the
     * MAIDAM-style engine; uses every per-kategori field on the model.
     */
    public static function apply(ProfilAsnaf $p): ProfilAsnaf
    {
        return HadAlKifayahService::apply($p);
    }
}
