<?php

namespace App\Services\Ai\Tools;

use App\Models\ProfilAsnaf;

/**
 * Returns dashboard counts + percentages as a structured stats card.
 */
class GetDashboardStatsTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match(
            '/\b(berapa|banyak|jumlah|statistik|statistic|stats|dashboard|count|total)\b/i',
            $message
        );
    }

    public function handle(string $message): array
    {
        $total = ProfilAsnaf::count();
        $layak = ProfilAsnaf::where('status_kelayakan', 'Layak')->count();
        $tidak = ProfilAsnaf::where('status_kelayakan', 'Tidak Layak')->count();
        $semakan = ProfilAsnaf::where('status_kelayakan', 'Dalam Semakan')->count();
        $pctLayak = $total > 0 ? round(($layak / $total) * 100, 1) : 0;
        $pctTidak = $total > 0 ? round(($tidak / $total) * 100, 1) : 0;

        return [
            'reply' => 'Berikut adalah statistik pemohon semasa:',
            'data' => [
                'type' => 'stats',
                'title' => 'Statistik Pemohon',
                'stats' => [
                    ['label' => 'Jumlah Pemohon', 'value' => $total,    'color' => 'primary', 'pct' => '100%'],
                    ['label' => 'Layak',          'value' => $layak,    'color' => 'success', 'pct' => "{$pctLayak}%"],
                    ['label' => 'Tidak Layak',    'value' => $tidak,    'color' => 'danger',  'pct' => "{$pctTidak}%"],
                    ['label' => 'Dalam Semakan',  'value' => $semakan,  'color' => 'warning', 'pct' => $total > 0 ? round(($semakan / $total) * 100, 1).'%' : '—'],
                ],
            ],
        ];
    }

    public function label(): string
    {
        return 'Statistik dashboard';
    }

    public function icon(): string
    {
        return 'fa-chart-pie';
    }
}
