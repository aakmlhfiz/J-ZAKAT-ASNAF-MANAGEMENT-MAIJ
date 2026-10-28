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
 *
 * Function names, parameter keys and enums are stable API surface and must
 * never be translated. Only descriptions are localized.
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
                    'description' => __('ai.tools.get_applicant.description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'ic' => [
                                'type' => 'string',
                                'description' => __('ai.tools.get_applicant.ic_description'),
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
                    'description' => __('ai.tools.search_applicants.description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => __('ai.tools.search_applicants.query_description'),
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
                    'description' => __('ai.tools.dashboard_stats.description'),
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
                    'description' => __('ai.tools.explain_eligibility.description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'ic' => [
                                'type' => 'string',
                                'description' => __('ai.tools.explain_eligibility.ic_description'),
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => __('ai.tools.explain_eligibility.name_description'),
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
                    'description' => __('ai.tools.calculate_kifayah.description'),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'pendapatan_pemohon' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.applicant_income')],
                            'pendapatan_pasangan' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.spouse_income')],
                            'pendapatan_anak' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.children_income')],
                            'pendapatan_lain' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.other_income')],
                            'n_isteri_suami' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_ibu_bapa' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_dewasa_tidak_bekerja' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_dewasa_bekerja_tanggung' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_ipt' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_remaja' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_kanak_rendah' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'n_kanak_tadika' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20],
                            'perumahan' => ['type' => 'string', 'enum' => ['sendiri', 'sewa']],
                            'utiliti_input' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.utilities_description')],
                            'pengangkutan_input' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.transport_description')],
                            'perubatan_input' => ['type' => 'number', 'minimum' => 0, 'description' => __('ai.tools.calculate_kifayah.medical_description')],
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
            default => $this->toolError(__('ai.errors.unknown_tool', ['name' => $name])),
        };
    }

    private function getApplicant(array $arguments): array
    {
        $ic = preg_replace('/\D+/', '', (string) ($arguments['ic'] ?? ''));

        if ($ic === '') {
            return $this->toolError(__('ai.tools.get_applicant.missing_ic'));
        }

        return (new GetApplicantTool)->handle($ic);
    }

    private function searchApplicants(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $query = trim((string) preg_replace('/^(cari|find|search)\s+/i', '', $query));

        if ($query === '') {
            return $this->toolError(__('ai.tools.search_applicants.missing_query'));
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

        return $this->toolError(__('ai.tools.explain_eligibility.missing_input'));
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
            return $this->toolError(__('ai.tools.calculate_kifayah.missing_input'));
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
