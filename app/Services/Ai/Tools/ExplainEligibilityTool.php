<?php

namespace App\Services\Ai\Tools;

use App\Models\ProfilAsnaf;

class ExplainEligibilityTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match(
            '/\b(adakah|apakah|layak|eligible|explain|terangkan|huraikan|mengapa|why)\b/i',
            $message
        );
    }

    public function handle(string $message): array
    {
        $ic = null;
        if (preg_match('/\b(\d{10,14})\b/', $message, $m)) {
            $ic = $m[1];
        }

        $p = $ic ? ProfilAsnaf::where('ic', $ic)->first() : null;

        if (! $p && preg_match('/\b([A-Z][a-zA-Z]+(?:\s+[A-Z][a-zA-Z]+)+)\b/', $message, $m)) {
            $p = ProfilAsnaf::where('nama', 'like', "%{$m[1]}%")->first();
        }

        if (! $p) {
            return [
                'reply' => 'Sila berikan No. IC atau nama penuh pemohon. Contoh: *Adakah 011212083433 layak?*',
                'data' => ['type' => 'text', 'needs_input' => true],
            ];
        }

        $pendapatan = (float) $p->pendapatan;
        $hadKifayah = (float) $p->had_kifayah;
        $tanggungan = (int) $p->bilangan_tanggungan;
        $gap = $hadKifayah - $pendapatan;

        $totalKandungan = (int) $p->n_isteri_suami + (int) $p->n_ibu_bapa
            + (int) $p->n_dewasa_tidak_bekerja + (int) $p->n_dewasa_bekerja_tanggung
            + (int) $p->n_ipt + (int) $p->n_remaja
            + (int) $p->n_kanak_rendah + (int) $p->n_kanak_tadika;
        $totalPerbelanjaan = (float) $p->utiliti + (float) $p->pengangkutan_input + (float) $p->perubatan_input;

        $items = [];
        if ($pendapatan < $hadKifayah) {
            $items[] = '✓ Pendapatan (RM '.number_format($pendapatan, 2).') lebih rendah daripada Had Kifayah (RM '.number_format($hadKifayah, 2).') — jurang RM '.number_format($gap, 2);
        } else {
            $items[] = '✗ Pendapatan (RM '.number_format($pendapatan, 2).') melebihi Had Kifayah (RM '.number_format($hadKifayah, 2).')';
        }
        if ($totalKandungan >= 3) {
            $items[] = "✓ Bilangan tanggungan tinggi ({$totalKandungan} orang)";
        } elseif ($totalKandungan > 0) {
            $items[] = "○ Bilangan tanggungan sederhana ({$totalKandungan} orang)";
        } else {
            $items[] = "○ Bilangan tanggungan tidak direkodkan (guna bilangan_tanggungan: {$tanggungan})";
        }
        if ($totalPerbelanjaan > 0) {
            $items[] = '✓ Perbelanjaan tetap (utiliti + pengangkutan + perubatan): RM '.number_format($totalPerbelanjaan, 2);
        } else {
            $items[] = '○ Tiada sewa / utiliti / pengangkutan / perubatan direkodkan';
        }
        if ($p->perumahan === 'sewa') {
            $items[] = '○ Tempat tinggal: rumah sewa (tambahan kadar sewa)';
        }

        $status = $p->status_kelayakan;
        $confidence = (float) $p->ai_score > 0
            ? (int) $p->ai_score
            : ($status === 'Layak' ? 92 : ($status === 'Tidak Layak' ? 78 : 50));

        return [
            'reply' => "Berikut adalah penjelasan kelayakan untuk **{$p->nama}** ({$p->ic}):",
            'data' => [
                'type' => 'checklist',
                'title' => 'Keputusan Kelayakan',
                'status' => $status,
                'confidence' => $confidence,
                'items' => $items,
                'had_kifayah' => $hadKifayah,
                'pendapatan' => $pendapatan,
                'gap' => $gap,
            ],
        ];
    }

    public function label(): string
    {
        return 'Terangkan kelayakan pemohon';
    }

    public function icon(): string
    {
        return 'fa-circle-check';
    }
}
