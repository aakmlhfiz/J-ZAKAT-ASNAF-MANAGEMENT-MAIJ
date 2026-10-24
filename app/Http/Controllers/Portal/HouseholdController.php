<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PortalHouseholdService;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    public function show(): View
    {
        $user = auth('portal')->user();
        $household = PortalHouseholdService::resolve($user);
        $members = PortalHouseholdService::members($household);
        $ketua = PortalHouseholdService::ketua($household);
        $summary = PortalHouseholdService::summary($household);
        $assistanceByMember = PortalHouseholdService::assistanceByMember($household);

        return view('portal.household.index', compact('user', 'household', 'members', 'ketua', 'summary', 'assistanceByMember'));
    }

    public function assistance(): View
    {
        $user = auth('portal')->user();
        $household = PortalHouseholdService::resolve($user);
        $members = PortalHouseholdService::members($household);
        $assistanceByMember = PortalHouseholdService::assistanceByMember($household);
        $totals = PortalHouseholdService::assistanceTotals($household);

        return view('portal.household.assistance', compact('user', 'household', 'members', 'assistanceByMember', 'totals'));
    }

    public function finance(): View
    {
        $user = auth('portal')->user();
        $household = PortalHouseholdService::resolve($user);
        $members = PortalHouseholdService::members($household);
        $summary = PortalHouseholdService::summary($household);

        $income = [
            'applicant' => 0.0,
            'spouse' => 0.0,
            'other' => 0.0,
        ];

        foreach ($members as $member) {
            $rel = mb_strtolower(trim((string) $member->hubungan));
            $amount = (float) $member->pendapatan_bulanan;

            if ($rel === 'ketua isi rumah') {
                $income['applicant'] += $amount;
            } elseif (in_array($rel, ['isteri', 'suami', 'isteri / suami'], true)) {
                $income['spouse'] += $amount;
            } else {
                $income['other'] += $amount;
            }
        }

        if ($income['applicant'] === 0.0) {
            $income['applicant'] = (float) ($household?->total_income ?? 0);
        }

        return view('portal.household.finance', compact('user', 'household', 'summary', 'income'));
    }
}
