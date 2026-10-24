<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ProfilAsnaf;
use Carbon\Carbon;

class HouseholdService
{
    public function findById(string $id): ?Household
    {
        return Household::with(['members.assistanceHistories', 'assistanceHistories.member'])
            ->find($id);
    }

    public function findByHouseholdId(string $householdId): ?Household
    {
        return Household::where('household_id', $householdId)
            ->with(['members.assistanceHistories', 'assistanceHistories.member'])
            ->first();
    }

    public function searchByIC(string $ic): ?array
    {
        $member = HouseholdMember::with('household')->where('ic', $ic)->first();

        if (! $member) {
            return null;
        }

        $household = $this->findById($member->household_id);

        return [
            'household' => $household,
            'matched_member' => $member,
            'match_type' => $member->hubungan,
        ];
    }

    public function createFromProfilAsnaf(ProfilAsnaf $ketua, array $memberData = []): Household
    {
        $household = Household::create([
            'alamat' => $ketua->alamat,
            'total_income' => $ketua->pendapatan ?? 0,
            'total_expenses' => ($ketua->sewa ?? 0) + ($ketua->utiliti ?? 0)
                + ($ketua->pengangkutan_input ?? 0) + ($ketua->perubatan_input ?? 0),
            'ai_score' => $ketua->ai_score ?? 0,
            'eligibility_status' => $ketua->status_kelayakan ?? 'Dalam Semakan',
            'status' => 'active',
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'profil_asnaf_id' => $ketua->id,
            'nama' => $ketua->nama,
            'ic' => $ketua->ic,
            'hubungan' => 'Ketua Isi Rumah',
            'umur' => $this->calculateAgeFromIC($ketua->ic),
            'pekerjaan' => null,
            'pendidikan' => null,
            'pendapatan_bulanan' => $ketua->pendapatan ?? 0,
            'status_kesihatan' => null,
            'applicant_status' => 'active',
            'assistance_status' => $ketua->approval_status === 'approved' ? 'Menerima Bantuan' : null,
        ]);

        if ($ketua->ic_ketua_rumah) {
            $relatedMembers = ProfilAsnaf::where('ic_ketua_rumah', $ketua->ic)
                ->where('ic', '!=', $ketua->ic)
                ->get();

            foreach ($relatedMembers as $related) {
                HouseholdMember::create([
                    'household_id' => $household->id,
                    'profil_asnaf_id' => $related->id,
                    'nama' => $related->nama,
                    'ic' => $related->ic,
                    'hubungan' => $related->hubungan ?? 'Tanggungan',
                    'umur' => $this->calculateAgeFromIC($related->ic),
                    'pekerjaan' => null,
                    'pendidikan' => null,
                    'pendapatan_bulanan' => 0,
                    'status_kesihatan' => null,
                    'applicant_status' => 'active',
                    'assistance_status' => $related->approval_status === 'approved' ? 'Menerima Bantuan' : null,
                ]);
            }
        }

        return $household;
    }

    public function updateAggregates(Household $household): Household
    {
        $members = $household->members;

        $household->total_income = $members->sum('pendapatan_bulanan');
        $household->total_expenses = $household->total_expenses ?? 0;
        $household->save();

        return $household->fresh();
    }

    public function getBenefitMatrix(Household $household): array
    {
        $jenisList = array_keys(AssistanceHistory::jenisBantuanOptions());
        $members = $household->members()->with('assistanceHistories')->get();
        $matrix = [];

        foreach ($members as $member) {
            $row = [
                'member' => $member->nama,
                'hubungan' => $member->hubungan,
            ];
            foreach ($jenisList as $jenis) {
                $assistance = $member->assistanceHistories
                    ->where('jenis_bantuan', $jenis)
                    ->first();
                $row[$jenis] = $assistance ? [
                    'status' => $assistance->status,
                    'approved_date' => $assistance->approved_date?->format('Y-m-d'),
                    'expiry_date' => $assistance->expiry_date?->format('Y-m-d'),
                    'frequency' => $assistance->frequency,
                    'amount' => $assistance->amount,
                    'officer' => $assistance->approval_officer,
                    'reference' => $assistance->reference_number,
                ] : null;
            }
            $matrix[] = $row;
        }

        return [
            'columns' => $jenisList,
            'rows' => $matrix,
        ];
    }

    private function calculateAgeFromIC(string $ic): int
    {
        if (strlen($ic) < 6) {
            return 0;
        }

        $yy = (int) substr($ic, 0, 2);
        $mm = (int) substr($ic, 2, 2);
        $dd = (int) substr($ic, 4, 2);

        $year = $yy > 30 ? 1900 + $yy : 2000 + $yy;

        $birthDate = Carbon::createFromDate($year, $mm, $dd);

        return $birthDate->age;
    }
}
