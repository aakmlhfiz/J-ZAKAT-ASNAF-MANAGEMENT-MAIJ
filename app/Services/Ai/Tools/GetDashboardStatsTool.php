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
            'reply' => __('ai.tools.dashboard_stats.reply'),
            'data' => [
                'type' => 'stats',
                'title' => __('ai.tools.dashboard_stats.title'),
                'stats' => [
                    ['label' => __('ai.tools.dashboard_stats.total'),        'value' => $total,    'color' => 'primary', 'pct' => '100%'],
                    ['label' => __('ai.tools.dashboard_stats.eligible'),     'value' => $layak,    'color' => 'success', 'pct' => "{$pctLayak}%"],
                    ['label' => __('ai.tools.dashboard_stats.not_eligible'), 'value' => $tidak,    'color' => 'danger',  'pct' => "{$pctTidak}%"],
                    ['label' => __('ai.tools.dashboard_stats.under_review'), 'value' => $semakan,  'color' => 'warning', 'pct' => $total > 0 ? round(($semakan / $total) * 100, 1).'%' : '—'],
                ],
            ],
        ];
    }

    public function label(): string
    {
        return __('ai.tools.dashboard_stats.label');
    }

    public function icon(): string
    {
        return 'fa-chart-pie';
    }
}
