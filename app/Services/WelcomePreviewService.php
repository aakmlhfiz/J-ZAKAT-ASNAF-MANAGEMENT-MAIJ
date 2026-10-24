<?php

namespace App\Services;

use App\Models\ProfilAsnaf;

class WelcomePreviewService
{
    private const MALAY_MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mac', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ogos',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dis',
    ];

    public function getStats(): array
    {
        $total = ProfilAsnaf::count();
        $layak = ProfilAsnaf::where('status_kelayakan', 'Layak')->count();
        $tidakLayak = ProfilAsnaf::where('status_kelayakan', 'Tidak Layak')->count();

        return [
            'total' => $total,
            'layak' => $layak,
            'tidak_layak' => $tidakLayak,
            'dalam_semakan' => $total - $layak - $tidakLayak,
        ];
    }

    public function getChartData(): array
    {
        $chartLabels = [];
        $chartValues = [];

        $monthlyData = ProfilAsnaf::monthlyAggregate(12);

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $chartLabels[] = self::MALAY_MONTHS[(int) $date->format('n')];
            $chartValues[] = (int) $monthlyData->where('month', $monthKey)->sum('cnt');
        }

        if (array_sum($chartValues) === 0) {
            $chartLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
            $chartValues = [320, 412, 480, 510, 590, 640, 720, 810, 780, 860, 920, 1010];
        }

        return [
            'labels' => $chartLabels,
            'values' => $chartValues,
        ];
    }

    public function getAiPick(): ?array
    {
        $latest = ProfilAsnaf::whereNotNull('ai_score')->latest()->first();

        if (! $latest) {
            return null;
        }

        return [
            'name' => mb_strimwidth($latest->nama, 0, 12, '…'),
            'score' => $latest->confidenceScore(),
            'tone' => $latest->statusTone(),
            'status_label' => match ($latest->statusSlug()) {
                'layak' => 'Layak',
                'tidak-layak' => 'Tidak Layak',
                default => 'Semakan',
            },
        ];
    }

    public function getAll(): array
    {
        $chart = $this->getChartData();

        return [
            'stats' => $this->getStats(),
            'chartLabels' => $chart['labels'],
            'chartValues' => $chart['values'],
            'aiPick' => $this->getAiPick(),
        ];
    }
}
