<?php

namespace App\Services\Ai\Tools;

use App\Models\ProfilAsnaf;

/**
 * Fetches a single applicant by IC (12-digit match) and returns
 * a summary + explanation response.
 */
class GetApplicantTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match('/\b(\d{10,14})\b/', $message);
    }

    public function handle(string $message): array
    {
        preg_match('/\b(\d{10,14})\b/', $message, $m);
        $ic = $m[1];

        $p = ProfilAsnaf::where('ic', $ic)->first();

        if (! $p) {
            return [
                'reply' => "Tiada pemohon dengan No. IC **{$ic}** dijumpai dalam pangkalan data.",
                'data' => ['type' => 'text', 'ic' => $ic, 'not_found' => true],
            ];
        }

        $gap = (float) $p->had_kifayah - (float) $p->pendapatan;
        $reason = $p->status_kelayakan === 'Layak'
            ? 'Pendapatan berada di bawah Had Kifayah. Layak dipertimbangkan untuk bantuan.'
            : ($p->status_kelayakan === 'Tidak Layak'
                ? 'Pendapatan melebihi Had Kifayah. Tidak layak pada masa ini.'
                : 'Data kewangan belum lengkap.');

        return [
            'reply' => "Berikut adalah profil pemohon **{$p->nama}**:",
            'data' => [
                'type' => 'summary',
                'fields' => [
                    'Nama' => $p->nama,
                    'No. IC' => $p->ic,
                    'Pendapatan' => 'RM '.number_format((float) $p->pendapatan, 2),
                    'Tanggungan' => (int) $p->bilangan_tanggungan.' orang',
                    'Sewa' => 'RM '.number_format((float) $p->sewa, 2),
                    'Utiliti' => 'RM '.number_format((float) $p->utiliti, 2),
                    'Had Kifayah' => 'RM '.number_format((float) $p->had_kifayah, 2),
                    'Status' => $p->status_kelayakan,
                ],
                'reason' => $reason,
                'gap' => $gap,
            ],
        ];
    }

    public function label(): string
    {
        return 'Cari pemohon mengikut No. IC';
    }

    public function icon(): string
    {
        return 'fa-id-card';
    }
}
