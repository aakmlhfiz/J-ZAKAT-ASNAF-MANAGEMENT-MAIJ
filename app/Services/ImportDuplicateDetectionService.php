<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\HouseholdMember;
use App\Models\ProfilAsnaf;

class ImportDuplicateDetectionService
{
    public function analyze(array $extractedData): array
    {
        $warnings = [];
        $hasWarnings = false;
        $highSeverityCount = 0;

        $ic = $extractedData['ic']['value'] ?? null;
        $nama = $extractedData['nama']['value'] ?? null;
        $alamat = $extractedData['alamat']['value'] ?? null;

        if ($ic) {
            $existingProfil = ProfilAsnaf::where('ic', $ic)->first();
            if ($existingProfil) {
                $hasWarnings = true;
                $highSeverityCount++;

                $householdMember = HouseholdMember::with('household')->where('ic', $ic)->first();
                $householdInfo = null;

                if ($householdMember && $householdMember->household) {
                    $h = $householdMember->household;
                    $householdInfo = [
                        'household_id' => $h->household_id,
                        'id' => $h->id,
                        'head_of_household' => $h->ketua?->nama ?? 'Unknown',
                        'total_members' => $h->total_members,
                        'member_count' => $h->members()->count(),
                        'member_relationship' => $householdMember->hubungan,
                    ];
                }

                $previousAssistance = $this->getPreviousAssistance($ic);

                $warnings[] = [
                    'type' => 'duplicate_ic',
                    'severity' => 'high',
                    'title' => 'Existing Applicant Found',
                    'description' => "{$nama} (IC: {$ic}) already exists in the system.",
                    'details' => [
                        'existing_name' => $existingProfil->nama,
                        'status_kelayakan' => $existingProfil->status_kelayakan,
                        'approval_status' => $existingProfil->approval_status,
                        'ai_score' => $existingProfil->ai_score,
                        'household' => $householdInfo,
                        'previous_assistance' => $previousAssistance,
                    ],
                ];
            } else {
                $memberInHousehold = HouseholdMember::with('household')->where('ic', $ic)->first();
                if ($memberInHousehold) {
                    $h = $memberInHousehold->household;
                    $hasWarnings = true;
                    $highSeverityCount++;

                    $warnings[] = [
                        'type' => 'ic_in_household',
                        'severity' => 'high',
                        'title' => 'IC Found in Existing Household',
                        'description' => "This IC belongs to {$memberInHousehold->nama} in Household {$h->household_id} as {$memberInHousehold->hubungan}.",
                        'details' => [
                            'household_id' => $h->household_id,
                            'id' => $h->id,
                            'head_of_household' => $h->ketua?->nama ?? 'Unknown',
                            'member_relationship' => $memberInHousehold->hubungan,
                            'total_members' => $h->total_members,
                        ],
                    ];
                }
            }
        }

        if ($alamat) {
            $addressMatches = ProfilAsnaf::where('alamat', 'like', '%'.mb_substr($alamat, 0, 20).'%')
                ->where('ic', '!=', $ic)
                ->limit(3)
                ->get();

            if ($addressMatches->isNotEmpty()) {
                $hasWarnings = true;
                $names = $addressMatches->pluck('nama')->implode(', ');
                $warnings[] = [
                    'type' => 'duplicate_address',
                    'severity' => 'medium',
                    'title' => 'Similar Address Detected',
                    'description' => "Address matches {$addressMatches->count()} existing applicant(s): {$names}.",
                    'details' => [
                        'matching_applicants' => $addressMatches->map(fn ($p) => ['nama' => $p->nama, 'ic' => $p->ic])->toArray(),
                    ],
                ];
            }
        }

        if ($ic) {
            $member = HouseholdMember::with('assistanceHistories')->where('ic', $ic)->first();
            if ($member && $member->assistanceHistories()->where('status', 'approved')->exists()) {
                $activeAssistance = $member->assistanceHistories()
                    ->where('status', 'approved')
                    ->get();

                $hasWarnings = true;
                $items = $activeAssistance->pluck('jenis_bantuan')->implode(', ');
                $warnings[] = [
                    'type' => 'active_assistance',
                    'severity' => 'medium',
                    'title' => 'Active Assistance Detected',
                    'description' => "This person currently receives: {$items}.",
                    'details' => [
                        'assistance_items' => $activeAssistance->map(fn ($a) => [
                            'jenis_bantuan' => $a->jenis_bantuan,
                            'amount' => $a->amount,
                            'frequency' => $a->frequency,
                            'approved_date' => $a->approved_date?->format('Y-m-d'),
                        ])->toArray(),
                    ],
                ];
            }
        }

        if ($ic && $ic_ketua = ($extractedData['ic_ketua_rumah']['value'] ?? null)) {
            $ketuaHousehold = HouseholdMember::with('household')->where('ic', $ic_ketua)->first();
            if ($ketuaHousehold) {
                $warnings[] = [
                    'type' => 'ketua_household_exists',
                    'severity' => 'low',
                    'title' => 'Head of Household Found',
                    'description' => "Head IC {$ic_ketua} has existing household {$ketuaHousehold->household->household_id}.",
                    'details' => [
                        'household_id' => $ketuaHousehold->household->household_id,
                        'head_name' => $ketuaHousehold->nama,
                    ],
                ];
            }
        }

        return [
            'has_warnings' => $hasWarnings,
            'warning_count' => count($warnings),
            'high_severity_count' => $highSeverityCount,
            'warnings' => $warnings,
        ];
    }

    private function getPreviousAssistance(string $ic): array
    {
        $member = HouseholdMember::where('ic', $ic)->first();
        if (! $member) {
            return [];
        }

        return AssistanceHistory::where('household_member_id', $member->id)
            ->latest('approved_date')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'jenis_bantuan' => $a->jenis_bantuan,
                'amount' => $a->amount,
                'frequency' => $a->frequency,
                'status' => $a->status,
                'approved_date' => $a->approved_date?->format('Y-m-d'),
                'expiry_date' => $a->expiry_date?->format('Y-m-d'),
            ])
            ->toArray();
    }
}
