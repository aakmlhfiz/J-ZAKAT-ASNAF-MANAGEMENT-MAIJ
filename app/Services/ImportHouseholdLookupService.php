<?php

namespace App\Services;

use App\Models\HouseholdMember;

class ImportHouseholdLookupService
{
    public function lookupByIC(string $ic): ?array
    {
        $member = HouseholdMember::with([
            'household',
            'household.members',
            'assistanceHistories',
        ])->where('ic', $ic)->first();

        if (! $member) {
            return null;
        }

        $household = $member->household;
        if (! $household) {
            return null;
        }

        $headMember = $household->ketua;
        $allMembers = $household->members;

        $membersData = $allMembers->map(function ($m) {
            $assistanceItems = $m->assistanceHistories()
                ->latest('approved_date')
                ->limit(5)
                ->get()
                ->map(fn ($a) => [
                    'jenis_bantuan' => $a->jenis_bantuan,
                    'amount' => $a->amount,
                    'frequency' => $a->frequency,
                    'status' => $a->status,
                    'approved_date' => $a->approved_date?->format('Y-m-d'),
                ]);

            return [
                'id' => $m->id,
                'nama' => $m->nama,
                'ic' => $m->ic,
                'hubungan' => $m->hubungan,
                'umur' => $m->umur,
                'pekerjaan' => $m->pekerjaan,
                'pendapatan_bulanan' => $m->pendapatan_bulanan,
                'status_kesihatan' => $m->status_kesihatan,
                'applicant_status' => $m->applicant_status,
                'assistance_status' => $m->assistance_status,
                'previous_assistance' => $assistanceItems,
            ];
        })->values()->toArray();

        $monthlyAssistance = (float) $household->assistanceHistories()
            ->where('frequency', 'monthly')
            ->where('status', 'approved')
            ->sum('amount');

        $oneOffAssistance = (float) $household->assistanceHistories()
            ->where('frequency', 'one_off')
            ->where('status', 'approved')
            ->sum('amount');

        $allAssistance = $household->assistanceHistories()
            ->where('status', 'approved')
            ->latest('approved_date')
            ->get()
            ->map(fn ($a) => [
                'member_name' => $a->member?->nama ?? __('ocr.unknown'),
                'jenis_bantuan' => $a->jenis_bantuan,
                'amount' => $a->amount,
                'frequency' => $a->frequency,
                'approved_date' => $a->approved_date?->format('Y-m-d'),
                'expiry_date' => $a->expiry_date?->format('Y-m-d'),
            ]);

        return [
            'found' => true,
            'household_id' => $household->household_id,
            'id' => $household->id,
            'head_of_household' => $headMember?->nama ?? __('ocr.unknown'),
            'head_ic' => $headMember?->ic,
            'matched_member' => [
                'nama' => $member->nama,
                'ic' => $member->ic,
                'hubungan' => $member->hubungan,
                'is_head' => $member->hubungan === 'Ketua Isi Rumah',
            ],
            'total_members' => $household->total_members,
            'members' => $membersData,
            'total_monthly_assistance' => $monthlyAssistance,
            'total_one_off_assistance' => $oneOffAssistance,
            'total_assistance_received' => $monthlyAssistance + $oneOffAssistance,
            'active_assistance_count' => $household->active_assistance_count,
            'all_assistance' => $allAssistance->toArray(),
            'eligibility_status' => $household->eligibility_status,
            'total_income' => $household->total_income,
            'total_expenses' => $household->total_expenses,
        ];
    }
}
