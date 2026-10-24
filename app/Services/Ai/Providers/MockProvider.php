<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Tools\AbstractTool;
use App\Services\Ai\Tools\CalculateKifayahTool;
use App\Services\Ai\Tools\ExplainEligibilityTool;
use App\Services\Ai\Tools\GetApplicantTool;
use App\Services\Ai\Tools\GetDashboardStatsTool;
use App\Services\Ai\Tools\SearchApplicantsTool;

/**
 * Deterministic, offline mock provider. Pattern-matches the user's message
 * and dispatches to the appropriate Tool.
 */
class MockProvider implements AiProvider
{
    /** @var AbstractTool[] */
    private array $tools;

    public function __construct()
    {
        $this->tools = [
            new GetApplicantTool,
            new SearchApplicantsTool,
            new GetDashboardStatsTool,
            new CalculateKifayahTool,
            new ExplainEligibilityTool,
        ];
    }

    public function chat(string $message, array $history = []): array
    {
        $msg = trim($message);
        if ($msg === '') {
            return $this->fallback();
        }

        // Highest priority first: IC (12 digits) → explain eligibility
        // Then calculator (numbers + 'kira') → stats → search
        $priority = [
            'explain',      // adakah / layak / why / explain
            'get_applicant', // 12-digit IC
            'calculate',    // kira / had kifayah
            'search',       // cari / find
            'stats',        // berapa / statistik / dashboard
        ];

        $matched = [];
        foreach ($this->tools as $tool) {
            if ($tool->matches($msg)) {
                $matched[] = $tool;
            }
        }

        if (empty($matched)) {
            return $this->fallback();
        }

        // Pick the highest-priority match.
        $byClass = [];
        foreach ($matched as $tool) {
            $byClass[get_class($tool)] = $tool;
        }
        foreach ($priority as $name) {
            $candidate = match ($name) {
                'explain' => ExplainEligibilityTool::class,
                'get_applicant' => GetApplicantTool::class,
                'calculate' => CalculateKifayahTool::class,
                'search' => SearchApplicantsTool::class,
                'stats' => GetDashboardStatsTool::class,
            };
            if (isset($byClass[$candidate])) {
                return $byClass[$candidate]->handle($msg);
            }
        }

        return $matched[0]->handle($msg);
    }

    public function name(): string
    {
        return 'mock';
    }

    private function fallback(): array
    {
        $suggestions = array_map(fn ($t) => [
            'id' => substr(md5(get_class($t)), 0, 6),
            'icon' => $t->icon(),
            'label' => $t->label(),
            'prompt' => $this->examplePromptFor($t),
        ], $this->tools);

        return [
            'reply' => "Saya boleh membantu anda dengan:\n\n".
                "• Cari profil pemohon (beri No. IC atau nama)\n".
                "• Kira Had Kifayah (contoh: *kira 1500 3 300 100*)\n".
                "• Jelaskan kelayakan pemohon (contoh: *adakah 011212083433 layak?*)\n".
                "• Papar statistik sistem\n".
                '• Cari rekod pemohon mengikut nama',
            'data' => [
                'type' => 'suggestions',
                'suggestions' => $suggestions,
            ],
        ];
    }

    private function examplePromptFor(AbstractTool $tool): string
    {
        return match (get_class($tool)) {
            GetApplicantTool::class => 'Berikan profil pemohon 011212083433',
            SearchApplicantsTool::class => 'Cari rekod pemohon bernama Ahmad',
            GetDashboardStatsTool::class => 'Berapa jumlah asnaf semasa?',
            CalculateKifayahTool::class => 'Kira Had Kifayah 1500 3 300 100',
            ExplainEligibilityTool::class => 'Adakah pemohon 011212083433 layak?',
            default => 'Tolong bantu saya',
        };
    }
}
