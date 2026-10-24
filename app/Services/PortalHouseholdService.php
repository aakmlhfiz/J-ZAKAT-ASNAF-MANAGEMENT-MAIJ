<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\PortalUser;
use Illuminate\Support\Collection;

class PortalHouseholdService
{
    /**
     * Resolve the household belonging to the authenticated applicant via
     * their IC number. Returns null when the user has no IC or no household.
     */
    public static function resolve(PortalUser $user): ?Household
    {
        if (! $user->ic_no) {
            return null;
        }

        $member = HouseholdMember::where('ic', $user->ic_no)->first();

        return $member?->household;
    }

    /**
     * All members of the household, keyed by IC, with their assistance records.
     */
    public static function members(?Household $household): Collection
    {
        if (! $household) {
            return collect();
        }

        return $household->members()
            ->orderByRaw("CASE WHEN hubungan = 'Ketua Isi Rumah' THEN 0 ELSE 1 END")
            ->get();
    }

    public static function ketua(?Household $household): ?HouseholdMember
    {
        if (! $household) {
            return null;
        }

        return $household->members()->where('hubungan', 'Ketua Isi Rumah')->first();
    }

    public static function isTanggungan(HouseholdMember $member): bool
    {
        return $member->hubungan !== 'Ketua Isi Rumah';
    }

    public static function isStudent(HouseholdMember $member): bool
    {
        $value = mb_strtolower(trim((string) $member->pendidikan));

        return $value !== '' && $value !== 'null' && in_array($value, ['pelajar', 'ipt', 'sekolah', 'universiti', 'kolej'], true);
    }

    /**
     * Household summary for the Isi Rumah / Kewangan pages.
     */
    public static function summary(?Household $household): array
    {
        if (! $household) {
            return [
                'total_members' => 0,
                'total_tanggungan' => 0,
                'total_income' => 0.0,
                'total_expenses' => 0.0,
            ];
        }

        $members = $household->members;

        return [
            'total_members' => $members->count(),
            'total_tanggungan' => $members->where('hubungan', '!=', 'Ketua Isi Rumah')->count(),
            'total_income' => (float) $household->total_income,
            'total_expenses' => (float) $household->total_expenses,
        ];
    }

    /**
     * Assistance totals (approved only) across the household.
     */
    public static function assistanceTotals(?Household $household): array
    {
        if (! $household) {
            return [
                'monthly' => 0.0,
                'one_off' => 0.0,
                'total' => 0.0,
            ];
        }

        return [
            'monthly' => $household->total_monthly_assistance,
            'one_off' => $household->total_one_off_assistance,
            'total' => $household->total_assistance_received,
        ];
    }

    /**
     * Map of member id => assistance records, only approved.
     */
    public static function assistanceByMember(?Household $household): Collection
    {
        if (! $household) {
            return collect();
        }

        return AssistanceHistory::where('household_id', $household->id)
            ->where('status', 'approved')
            ->orderByDesc('approved_date')
            ->get()
            ->groupBy('household_member_id');
    }
}
