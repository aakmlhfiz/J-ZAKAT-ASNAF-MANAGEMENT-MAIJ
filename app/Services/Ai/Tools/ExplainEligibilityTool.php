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
                'reply' => __('ai.tools.explain_eligibility.needs_input_reply'),
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
            $items[] = __('ai.tools.explain_eligibility.income_below', [
                'income' => number_format($pendapatan, 2),
                'had_kifayah' => number_format($hadKifayah, 2),
                'gap' => number_format($gap, 2),
            ]);
        } else {
            $items[] = __('ai.tools.explain_eligibility.income_above', [
                'income' => number_format($pendapatan, 2),
                'had_kifayah' => number_format($hadKifayah, 2),
            ]);
        }
        if ($totalKandungan >= 3) {
            $items[] = trans_choice('ai.tools.explain_eligibility.dependants_high', $totalKandungan);
        } elseif ($totalKandungan > 0) {
            $items[] = trans_choice('ai.tools.explain_eligibility.dependants_medium', $totalKandungan);
        } else {
            $items[] = __('ai.tools.explain_eligibility.dependants_none', ['count' => $tanggungan]);
        }
        if ($totalPerbelanjaan > 0) {
            $items[] = __('ai.tools.explain_eligibility.fixed_expenses', ['amount' => number_format($totalPerbelanjaan, 2)]);
        } else {
            $items[] = __('ai.tools.explain_eligibility.no_expenses');
        }
        if ($p->perumahan === 'sewa') {
            $items[] = __('ai.tools.explain_eligibility.rental');
        }

        $status = $p->status_kelayakan;
        $confidence = (float) $p->ai_score > 0
            ? (int) $p->ai_score
            : ($status === 'Layak' ? 92 : ($status === 'Tidak Layak' ? 78 : 50));

        return [
            'reply' => __('ai.tools.explain_eligibility.reply', ['name' => $p->nama, 'ic' => $p->ic]),
            'data' => [
                'type' => 'checklist',
                'title' => __('ai.tools.explain_eligibility.title'),
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
        return __('ai.tools.explain_eligibility.label');
    }

    public function icon(): string
    {
        return 'fa-circle-check';
    }
}
