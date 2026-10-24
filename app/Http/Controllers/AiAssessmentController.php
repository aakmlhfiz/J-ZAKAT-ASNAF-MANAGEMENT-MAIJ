<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsnaf;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\HadAlKifayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssessmentController extends Controller
{
    public function index(): View
    {
        $items = ProfilAsnaf::latest()
            ->limit(50)
            ->get()
            ->map(function ($p) {
                $p->confidence = $p->confidenceScore();
                $p->recommendation = $this->recommendationFor($p);
                $p->summary = $this->summaryFor($p);

                return $p;
            });

        $totals = [
            'layak' => $items->where('status_kelayakan', HadAlKifayahService::STATUS_LAYAK)->count(),
            'tidak_layak' => $items->where('status_kelayakan', HadAlKifayahService::STATUS_TIDAK_LAYAK)->count(),
            'dalam_semakan' => $items->where('status_kelayakan', HadAlKifayahService::STATUS_DALAM_SEMAKAN)->count(),
        ];

        $avgConfidence = $items->count() > 0
            ? (int) round($items->avg('confidence'))
            : 0;

        return view('ai-assessment', compact('items', 'totals', 'avgConfidence'));
    }

    public function run(Request $request): RedirectResponse
    {
        $request->validate(['ic' => 'required|string']);
        $profil = ProfilAsnaf::where('ic', $request->ic)->firstOrFail();

        EligibilityService::apply($profil);
        $profil->save();

        AuditLogger::aiAssessmentRun(auth('web')->user(), $profil, $request);

        return back()->with('success', "Penilaian AI untuk {$profil->nama} dikemas kini. Skor: {$profil->ai_score}%.");
    }

    private function recommendationFor(ProfilAsnaf $p): string
    {
        return match ($p->status_kelayakan) {
            HadAlKifayahService::STATUS_LAYAK => 'Layak menerima bantuan berdasarkan analisis Had Kifayah.',
            HadAlKifayahService::STATUS_TIDAK_LAYAK => 'Tidak memenuhi garis kelayakan pada masa ini.',
            default => 'Penilaian masih belum lengkap.',
        };
    }

    private function summaryFor(ProfilAsnaf $p): string
    {
        $pendapatan = (float) $p->pendapatan;
        $hadKifayah = (float) $p->had_kifayah;
        $gap = $hadKifayah - $pendapatan;
        $tanggungan = (int) $p->bilangan_tanggungan;
        $kelayakan = $p->status_kelayakan;

        $gapText = $gap > 0
            ? 'kekurangan sebanyak RM '.number_format($gap, 2)
            : 'tiada kekurangan (pendapatan melebihi keperluan minimum)';

        $headline = match ($kelayakan) {
            HadAlKifayahService::STATUS_LAYAK => 'Profil ini dikesan LAYAK menerima bantuan. ',
            HadAlKifayahService::STATUS_TIDAK_LAYAK => 'Profil ini TIDAK LAYAK pada masa ini. ',
            default => 'Penilaian profil ini belum lengkap. ',
        };

        $body = match ($kelayakan) {
            HadAlKifayahService::STATUS_LAYAK => 'Pendapatan bulanan RM '.number_format($pendapatan, 2).' lebih rendah daripada Had Kifayah RM '.number_format($hadKifayah, 2)." (gap: $gapText), dengan $tanggungan orang tanggungan. Penilaian AI mengesyorkan profil ini disokong untuk menerima bantuan setelah pengesahan manual.",
            HadAlKifayahService::STATUS_TIDAK_LAYAK => 'Pendapatan bulanan RM '.number_format($pendapatan, 2).' melebihi Had Kifayah RM '.number_format($hadKifayah, 2).' (lebihan: RM '.number_format(abs($gap), 2).'). Penilaian AI tidak mengesyorkan bantuan buat masa ini; profil boleh dikemas kini jika keadaan kewangan berubah.',
            default => 'Data kewangan belum lengkap untuk menjalankan penilaian penuh. Sila pastikan pendapatan, tanggungan, sewa dan utiliti direkodkan dengan tepat sebelum penilaian AI boleh dijana.',
        };

        return $headline.$body;
    }
}
