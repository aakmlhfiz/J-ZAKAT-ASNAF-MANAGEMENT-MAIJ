<?php

namespace App\Services\Ai;

use App\Services\Ai\Tools\CalculateKifayahTool;
use App\Services\Ai\Tools\ExplainEligibilityTool;
use App\Services\Ai\Tools\GetApplicantTool;
use App\Services\Ai\Tools\GetDashboardStatsTool;
use App\Services\Ai\Tools\SearchApplicantsTool;

/**
 * OpenAI-compatible function definitions for the J-ZAKAT assistant,
 * plus the local dispatch layer that executes them safely.
 */
class ToolRegistry
{
    /**
     * Tool definitions sent to Groq in the `tools` payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_applicant',
                    'description' => 'Get the full asnaf applicant profile by 12-digit Malaysian IC number.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'ic' => [
                                'type' => 'string',
                                'description' => '12-digit IC number without hyphens.',
                            ],
                        ],
                        'required' => ['ic'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_applicants',
                    'description' => 'Search asnaf applicants by full or partial name, or by IC number.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Applicant name or IC number to search for.',
                            ],
                        ],
                        'required' => ['query'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_dashboard_stats',
                    'description' => 'Get current applicant counts and eligibility statistics.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'explain_eligibility',
                    'description' => 'Explain an applicant eligibility decision using their IC number or full name.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'ic' => [
                                'type' => 'string',
                                'description' => '12-digit IC number, if known.',
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'Applicant full name, if the IC is unknown.',
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'calculate_kifayah',
                    'description' => 'Calculate Had Kifayah and eligibility from household income and composition. Use only values supplied by the user or found through another tool.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'pendapatan_pemohon' => ['type' => 'number', 'minimum' => 0, 'description' => 'Applicant monthly income (RM).'],
                            'pendapatan_pasangan' => ['type' => 'number', 'minimum' => 0, 'description' => 'Spouse monthly income (RM).'],
                            'pendapatan_anak' => ['type' => 'number', 'minimum' => 0, 'description' => 'Children monthly income (RM).'],
                            'pendapatan_lain' => ['type' => 'number', 'minimum' => 0, 'description' => 'Other monthly income (RM).'],
                            'n_isteri_suami' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_ibu_bapa' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_dewasa_tidak_bekerja' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_dewasa_bekerja_tanggung' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_ipt' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_remaja' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_kanak_rendah' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_kanak_tadika' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'perumahan' => ['type' => 'string', 'enum' => ['sendiri', 'sewa']],
                            'utiliti_input' => ['type' => 'number', 'minimum' => 0, 'description' => 'Monthly utility cost (RM).'],
                            'pengangkutan_input' => ['type' => 'number', 'minimum' => 0, 'description' => 'Monthly transport cost (RM).'],
                            'perubatan_input' => ['type' => 'number', 'minimum' => 0, 'description' => 'Monthly medical cost (RM).'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Execute one local tool call.
     *
     * @return array{reply: string, data: array<string, mixed>}
     */
    public function execute(string $name, array $arguments): array
    {
        return match ($name) {
            'get_applicant' => $this->getApplicant($arguments),
            'search_applicants' => $this->searchApplicants($arguments),
            'get_dashboard_stats' => (new GetDashboardStatsTool)->handle('statistik'),
            'explain_eligibility' => $this->explainEligibility($arguments),
            'calculate_kifayah' => $this->calculateKifayah($arguments),
            default => $this->toolError("Tool tidak dikenali: {$name}"),
        };
    }

    private function getApplicant(array $arguments): array
    {
        $ic = preg_replace('/\D+/', '', (string) ($arguments['ic'] ?? ''));

        if ($ic === '') {
            return $this->toolError('No. IC diperlukan untuk mendapatkan profil pemohon.');
        }

        return (new GetApplicantTool)->handle($ic);
    }

    private function searchApplicants(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $query = trim((string) preg_replace('/^(cari|find|search)\s+/i', '', $query));

        if ($query === '') {
            return $this->toolError('Nama atau No. IC diperlukan untuk carian pemohon.');
        }

        return (new SearchApplicantsTool)->handle('cari '.$query);
    }

    private function explainEligibility(array $arguments): array
    {
        $ic = preg_replace('/\D+/', '', (string) ($arguments['ic'] ?? ''));

        if ($ic !== '') {
            return (new ExplainEligibilityTool)->handle('adakah '.$ic.' layak');
        }

        $name = trim((string) ($arguments['name'] ?? ''));

        if ($name !== '') {
            $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

            return (new ExplainEligibilityTool)->handle('adakah '.$name.' layak');
        }

        return $this->toolError('No. IC atau nama penuh diperlukan untuk menjelaskan kelayakan.');
    }

    private function calculateKifayah(array $arguments): array
    {
        $numericKeys = [
            'pendapatan_pemohon', 'pendapatan_pasangan', 'pendapatan_anak', 'pendapatan_lain',
            'n_isteri_suami', 'n_ibu_bapa', 'n_dewasa_tidak_bekerja', 'n_dewasa_bekerja_tanggung',
            'n_ipt', 'n_remaja', 'n_kanak_rendah', 'n_kanak_tadika',
            'utiliti_input', 'pengangkutan_input', 'perubatan_input',
        ];

        $hasValue = false;

        foreach ($numericKeys as $key) {
            if ((float) ($arguments[$key] ?? 0) > 0) {
                $hasValue = true;
                break;
            }
        }

        if (! $hasValue) {
            return $this->toolError('Sila berikan sekurang-kurangnya pendapatan atau maklumat isi rumah untuk pengiraan Had Kifayah.');
        }

        return (new CalculateKifayahTool)->calculate($arguments);
    }

    /**
     * @return array{reply: string, data: array<string, mixed>}
     */
    private function toolError(string $message): array
    {
        return [
            'reply' => $message,
            'data' => ['type' => 'text', 'error' => 'tool_input'],
        ];
    }
}
