<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;

class HouseholdDuplicateService
{
    public function detectDuplicates(Household $household): array
    {
        $warnings = [];
        $memberICs = $household->members->pluck('ic')->toArray();

        $duplicateMembers = $this->findDuplicateMembers($memberICs, $household->id);
        if (! empty($duplicateMembers)) {
            $warnings[] = [
                'type' => 'duplicate_member',
                'severity' => 'high',
                'title' => __('household.duplicates.member_title'),
                'description' => __('household.duplicates.member_description', ['ics' => implode(', ', $duplicateMembers)]),
            ];
        }

        $duplicateAssistance = $this->findDuplicateAssistance($household);
        if (! empty($duplicateAssistance)) {
            $warnings[] = [
                'type' => 'duplicate_assistance',
                'severity' => 'medium',
                'title' => __('household.duplicates.assistance_title'),
                'description' => __('household.duplicates.assistance_description', ['items' => implode(', ', $duplicateAssistance)]),
            ];
        }

        $duplicateAddress = $this->findDuplicateAddress($household);
        if (! empty($duplicateAddress)) {
            $warnings[] = [
                'type' => 'duplicate_address',
                'severity' => 'low',
                'title' => __('household.duplicates.address_title'),
                'description' => __('household.duplicates.address_description', [
                    'count' => count($duplicateAddress),
                    'households' => implode(', ', $duplicateAddress),
                ]),
            ];
        }

        $activeApplications = $this->findActiveApplications($household);
        if (! empty($activeApplications)) {
            $warnings[] = [
                'type' => 'active_application',
                'severity' => 'medium',
                'title' => __('household.duplicates.application_title'),
                'description' => __('household.duplicates.application_description', ['names' => implode(', ', $activeApplications)]),
            ];
        }

        return [
            'has_warnings' => ! empty($warnings),
            'warning_count' => count($warnings),
            'warnings' => $warnings,
        ];
    }

    private function findDuplicateMembers(array $ics, int $excludeHouseholdId): array
    {
        return HouseholdMember::whereIn('ic', $ics)
            ->where('household_id', '!=', $excludeHouseholdId)
            ->pluck('nama')
            ->toArray();
    }

    private function findDuplicateAssistance(Household $household): array
    {
        $duplicates = [];
        $memberIds = $household->members->pluck('id')->toArray();

        $result = AssistanceHistory::whereIn('household_member_id', $memberIds)
            ->where('status', 'approved')
            ->selectRaw('household_member_id, jenis_bantuan, COUNT(*) as cnt')
            ->groupBy('household_member_id', 'jenis_bantuan')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($result as $row) {
            $member = $household->members->firstWhere('id', $row->household_member_id);
            if ($member) {
                $duplicates[] = $member->nama.' ('.$row->jenis_bantuan.' x'.$row->cnt.')';
            }
        }

        return $duplicates;
    }

    private function findDuplicateAddress(Household $household): array
    {
        if (empty($household->alamat)) {
            return [];
        }

        return Household::where('id', '!=', $household->id)
            ->where('alamat', $household->alamat)
            ->pluck('household_id')
            ->toArray();
    }

    private function findActiveApplications(Household $household): array
    {
        $active = [];
        foreach ($household->members as $member) {
            if ($member->assistanceHistories()
                ->where('status', 'approved')
                ->exists()) {
                $active[] = $member->nama;
            }
        }

        return $active;
    }
}
