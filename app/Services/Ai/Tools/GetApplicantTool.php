<?php

namespace App\Services\Ai\Tools;

use App\Models\ProfilAsnaf;

/**
 * Fetches a single applicant by IC (12-digit match) and returns
 * a summary + explanation response.
 */
class GetApplicantTool extends AbstractTool
{
    public function matches(string $message): bool
    {
        return (bool) preg_match('/\b(\d{10,14})\b/', $message);
    }

    public function handle(string $message): array
    {
        preg_match('/\b(\d{10,14})\b/', $message, $m);
        $ic = $m[1];

        $p = ProfilAsnaf::where('ic', $ic)->first();

        if (! $p) {
            return [
                'reply' => __('ai.tools.get_applicant.not_found', ['ic' => $ic]),
                'data' => ['type' => 'text', 'ic' => $ic, 'not_found' => true],
            ];
        }

        $gap = (float) $p->had_kifayah - (float) $p->pendapatan;
        $reason = match ($p->status_kelayakan) {
            'Layak' => __('ai.tools.get_applicant.reason_eligible'),
            'Tidak Layak' => __('ai.tools.get_applicant.reason_not_eligible'),
            default => __('ai.tools.get_applicant.reason_incomplete'),
        };

        return [
            'reply' => __('ai.tools.get_applicant.reply', ['name' => $p->nama]),
            'data' => [
                'type' => 'summary',
                'fields' => [
                    __('ai.fields.name') => $p->nama,
                    __('ai.fields.ic') => $p->ic,
                    __('ai.fields.income') => 'RM '.number_format((float) $p->pendapatan, 2),
                    __('ai.fields.dependants') => trans_choice('ai.values.people', (int) $p->bilangan_tanggungan),
                    __('ai.fields.rent') => 'RM '.number_format((float) $p->sewa, 2),
                    __('ai.fields.utilities') => 'RM '.number_format((float) $p->utiliti, 2),
                    __('ai.fields.had_kifayah') => 'RM '.number_format((float) $p->had_kifayah, 2),
                    __('ai.fields.status') => $this->statusLabel($p->status_kelayakan),
                ],
                'reason' => $reason,
                'gap' => $gap,
            ],
        ];
    }

    public function label(): string
    {
        return __('ai.tools.get_applicant.label');
    }

    public function icon(): string
    {
        return 'fa-id-card';
    }
}
