<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsnaf;
use App\Services\HadAlKifayahService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $total = ProfilAsnaf::count();
        $layak = ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_LAYAK)->count();
        $tidakLayak = ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_TIDAK_LAYAK)->count();
        $dalamSemakan = ProfilAsnaf::where('status_kelayakan', HadAlKifayahService::STATUS_DALAM_SEMAKAN)->count();

        $trends = $this->computeTrends();
        $sparklines = $this->computeSparklines();

        $stats = [
            'total' => $total,
            'layak' => $layak,
            'tidak_layak' => $tidakLayak,
            'dalam_semakan' => $dalamSemakan,
            'total_trend' => $trends['total'],
            'layak_trend' => $trends['layak'],
            'tidak_layak_trend' => $trends['tidak_layak'],
            'dalam_semakan_trend' => $trends['dalam_semakan'],
            'total_sparkline' => $sparklines['total'],
            'layak_sparkline' => $sparklines['layak'],
            'tidak_layak_sparkline' => $sparklines['tidak_layak'],
            'dalam_semakan_sparkline' => $sparklines['dalam_semakan'],
        ];

        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->locale(app()->getLocale())->translatedFormat('M');
            $values[] = (int) ($sparklines['total_map'][$key] ?? 0);
        }

        $recent = ProfilAsnaf::latest()->limit(5)->get();
        $latest = ProfilAsnaf::latest()->limit(8)->get();

        return view('dashboard', compact('stats', 'labels', 'values', 'recent', 'latest'));
    }

    private function computeTrends(): array
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $getCount = fn ($start, $end, $status = null) => ProfilAsnaf::when($status, fn ($q) => $q->where('status_kelayakan', $status))
            ->whereBetween('created_at', [$start, $end])->count();

        return [
            'total' => $this->formatTrend($getCount($currentMonth, now()), $getCount($previousMonth, (clone $previousMonth)->endOfMonth())),
            'layak' => $this->formatTrend($getCount($currentMonth, now(), HadAlKifayahService::STATUS_LAYAK), $getCount($previousMonth, (clone $previousMonth)->endOfMonth(), HadAlKifayahService::STATUS_LAYAK)),
            'tidak_layak' => $this->formatTrend($getCount($currentMonth, now(), HadAlKifayahService::STATUS_TIDAK_LAYAK), $getCount($previousMonth, (clone $previousMonth)->endOfMonth(), HadAlKifayahService::STATUS_TIDAK_LAYAK)),
            'dalam_semakan' => $this->formatTrend($getCount($currentMonth, now(), HadAlKifayahService::STATUS_DALAM_SEMAKAN), $getCount($previousMonth, (clone $previousMonth)->endOfMonth(), HadAlKifayahService::STATUS_DALAM_SEMAKAN)),
        ];
    }

    private function computeSparklines(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $result = [
            'total' => array_fill_keys($months, 0),
            'layak' => array_fill_keys($months, 0),
            'tidak_layak' => array_fill_keys($months, 0),
            'dalam_semakan' => array_fill_keys($months, 0),
            'total_map' => [],
        ];

        $rows = ProfilAsnaf::monthlyAggregate();

        foreach ($rows as $row) {
            if (! isset($result['total'][$row->month])) {
                continue;
            }
            $result['total'][$row->month] += $row->cnt;

            match ($row->status_kelayakan) {
                HadAlKifayahService::STATUS_LAYAK => $result['layak'][$row->month] = $row->cnt,
                HadAlKifayahService::STATUS_TIDAK_LAYAK => $result['tidak_layak'][$row->month] = $row->cnt,
                HadAlKifayahService::STATUS_DALAM_SEMAKAN => $result['dalam_semakan'][$row->month] = $row->cnt,
                default => null,
            };
        }

        $result['total_map'] = $result['total'];

        return [
            'total' => array_values($result['total']),
            'layak' => array_values($result['layak']),
            'tidak_layak' => array_values($result['tidak_layak']),
            'dalam_semakan' => array_values($result['dalam_semakan']),
            'total_map' => $result['total_map'],
        ];
    }

    private function formatTrend(int $current, int $previous): ?string
    {
        if ($previous === 0) {
            return $current > 0 ? __('dashboard.trend.new') : null;
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign.number_format($change, 1).'%';
    }
}
