<?php

namespace App\Http\Controllers;

use App\Exports\ApplicantsExport;
use App\Models\ProfilAsnaf;
use App\Services\HadAlKifayahService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $monthly = [];
        $trendLabels = [];
        $trendValues = [];
        $trendLayak = [];
        $trendTidak = [];

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthly[$key] = [
                'bulan' => now()->subMonths($i)->format('M Y'),
                'jumlah' => 0,
                'layak' => 0,
                'tidak_layak' => 0,
                'semakan' => 0,
                'pct_layak' => 0,
            ];
            $trendLabels[] = now()->subMonths($i)->format('M');
            $trendValues[] = 0;
            $trendLayak[] = 0;
            $trendTidak[] = 0;
            $months[] = $key;
        }

        $rows = ProfilAsnaf::monthlyAggregate();
        foreach ($rows as $row) {
            if (! isset($monthly[$row->month])) {
                continue;
            }
            $monthly[$row->month]['jumlah'] += $row->cnt;
            match ($row->status_kelayakan) {
                HadAlKifayahService::STATUS_LAYAK => $monthly[$row->month]['layak'] = $row->cnt,
                HadAlKifayahService::STATUS_TIDAK_LAYAK => $monthly[$row->month]['tidak_layak'] = $row->cnt,
                HadAlKifayahService::STATUS_DALAM_SEMAKAN => $monthly[$row->month]['semakan'] = $row->cnt,
                default => null,
            };
        }

        $monthly = array_values($monthly);
        foreach ($monthly as $i => $m) {
            $monthly[$i]['pct_layak'] = $m['jumlah'] > 0 ? round(($m['layak'] / $m['jumlah']) * 100, 1) : 0;
            $trendValues[$i] = $m['jumlah'];
            $trendLayak[$i] = $m['layak'];
            $trendTidak[$i] = $m['tidak_layak'];
        }

        $totals = [
            'jumlah' => ProfilAsnaf::count(),
            'layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_LAYAK)->count(),
            'tidak_layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_TIDAK_LAYAK)->count(),
            'semakan' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_DALAM_SEMAKAN)->count(),
        ];

        return view('reports.index', compact('monthly', 'totals', 'trendLabels', 'trendValues', 'trendLayak', 'trendTidak'));
    }

    public function exportPdf(Request $request)
    {
        $monthly = [];
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthly[$key] = [
                'bulan' => now()->subMonths($i)->format('M Y'),
                'jumlah' => 0,
                'layak' => 0,
                'tidak' => 0,
                'semakan' => 0,
                'pct_layak' => 0,
            ];
            $months[] = $key;
        }

        $rows = ProfilAsnaf::monthlyAggregate();
        foreach ($rows as $row) {
            if (! isset($monthly[$row->month])) {
                continue;
            }
            $monthly[$row->month]['jumlah'] += $row->cnt;
            match ($row->status_kelayakan) {
                HadAlKifayahService::STATUS_LAYAK => $monthly[$row->month]['layak'] = $row->cnt,
                HadAlKifayahService::STATUS_TIDAK_LAYAK => $monthly[$row->month]['tidak'] = $row->cnt,
                HadAlKifayahService::STATUS_DALAM_SEMAKAN => $monthly[$row->month]['semakan'] = $row->cnt,
                default => null,
            };
        }

        $monthly = array_values($monthly);
        foreach ($monthly as $i => $m) {
            $monthly[$i]['pct_layak'] = $m['jumlah'] > 0 ? round(($m['layak'] / $m['jumlah']) * 100, 1) : 0;
        }

        $totals = [
            'jumlah' => ProfilAsnaf::count(),
            'layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_LAYAK)->count(),
            'tidak_layak' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_TIDAK_LAYAK)->count(),
            'semakan' => ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_DALAM_SEMAKAN)->count(),
        ];

        $applicants = ProfilAsnaf::latest()
            ->when(request('status'), fn ($q, $s) => $q->where('status_kelayakan', $s))
            ->limit(500)
            ->get();

        $pdf = Pdf::loadView('reports.pdf', compact('monthly', 'totals', 'applicants'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-j-zakat-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ApplicantsExport,
            'laporan-j-zakat-'.now()->format('Y-m-d').'.xlsx'
        );
    }
}
