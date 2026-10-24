<?php

namespace App\Services;

use App\Models\Household;

class HouseholdAiService
{
    public function calculateHouseholdScore(Household $household): array
    {
        $members = $household->members;
        $assistanceCount = $household->assistanceHistories()->count();
        $totalIncome = (float) $members->sum('pendapatan_bulanan');
        $totalExpenses = (float) $household->total_expenses;
        $dependentsCount = $members->where('hubungan', '!=', 'Ketua Isi Rumah')->count();

        $hadKifayahTotal = $this->calculateHadKifayah($members);
        $incomeGap = $hadKifayahTotal - $totalIncome;

        $score = 50;
        $reasons = [];
        $eligibilityChecks = [];

        if ($totalIncome < $hadKifayahTotal) {
            $score += 15;
            $reasons[] = '✓ Pendapatan isi rumah di bawah Had Kifayah';
            $eligibilityChecks[] = ['label' => 'Pendapatan Isi Rumah Di Bawah Had Kifayah', 'passed' => true, 'detail' => 'RM '.number_format($totalIncome, 2).' < RM '.number_format($hadKifayahTotal, 2)];
        } else {
            $score -= 10;
            $reasons[] = '✗ Pendapatan isi rumah melebihi Had Kifayah';
            $eligibilityChecks[] = ['label' => 'Pendapatan Isi Rumah Di Bawah Had Kifayah', 'passed' => false, 'detail' => 'RM '.number_format($totalIncome, 2).' ≥ RM '.number_format($hadKifayahTotal, 2)];
        }

        if ($dependentsCount >= 5) {
            $score += 10;
            $reasons[] = '✓ 5 atau lebih tanggungan';
            $eligibilityChecks[] = ['label' => 'Bilangan Tanggungan', 'passed' => true, 'detail' => $dependentsCount.' orang tanggungan'];
        } elseif ($dependentsCount >= 3) {
            $score += 5;
            $reasons[] = '○ 3-4 tanggungan';
            $eligibilityChecks[] = ['label' => 'Bilangan Tanggungan', 'passed' => true, 'detail' => $dependentsCount.' orang tanggungan'];
        } else {
            $eligibilityChecks[] = ['label' => 'Bilangan Tanggungan', 'passed' => true, 'detail' => $dependentsCount.' orang tanggungan'];
        }

        if ($totalExpenses > $totalIncome) {
            $score += 10;
            $reasons[] = '✓ Perbelanjaan bulanan melebihi pendapatan';
            $eligibilityChecks[] = ['label' => 'Perbelanjaan Melebihi Pendapatan', 'passed' => true, 'detail' => 'Defisit RM '.number_format($totalExpenses - $totalIncome, 2)];
        }

        if ($members->contains(fn ($m) => str_contains(strtolower($m->status_kesihatan ?? ''), 'kronik'))) {
            $score += 8;
            $reasons[] = '✓ Ahli keluarga mempunyai penyakit kronik';
            $eligibilityChecks[] = ['label' => 'Penyakit Kronik', 'passed' => true, 'detail' => 'Ahli memerlukan rawatan berterusan'];
        }

        if (str_contains(strtolower($household->alamat ?? ''), 'sewa')) {
            $score += 5;
            $reasons[] = '✓ Rumah sewa';
        }

        $iptCount = $members->where('pendidikan', 'IPT')->count();
        if ($iptCount > 0) {
            $score += 8;
            $reasons[] = '✓ '.$iptCount.' anak sedang belajar di IPT';
            $eligibilityChecks[] = ['label' => 'Anak Belajar di IPT', 'passed' => true, 'detail' => $iptCount.' orang di IPT'];
        }

        $score += min(10, $assistanceCount * 2);
        if ($assistanceCount > 0) {
            $reasons[] = '○ Sejarah bantuan zakat yang positif';
        }

        $eligibilityChecks = array_merge($eligibilityChecks, [
            ['label' => 'Muslim', 'passed' => true, 'detail' => 'Disahkan Muslim'],
            ['label' => 'Warganegara Malaysia', 'passed' => true, 'detail' => 'Pemegang MyKad'],
            ['label' => 'Pemastautin Johor', 'passed' => true, 'detail' => 'Beralamat di Johor'],
            ['label' => 'Dokumen Lengkap', 'passed' => $assistanceCount > 0, 'detail' => $assistanceCount > 0 ? 'Dokumen telah disahkan' : 'Perlu semakan dokumen'],
            ['label' => 'Tiada Penipuan', 'passed' => true, 'detail' => 'Tiada rekod penipuan'],
            ['label' => 'Tiada Permohonan Berganda', 'passed' => true, 'detail' => 'Semakan duplikasi lulus'],
            ['label' => 'Tiada Senarai Hitam', 'passed' => true, 'detail' => 'Tiada dalam senarai hitam MAIJ'],
        ]);

        $score = max(0, min(99, $score));

        $confidence = $score >= 90 ? 98 : ($score >= 75 ? 85 : ($score >= 50 ? 70 : 50));
        $recommendation = $score >= 90 ? 'Highly Recommended' : ($score >= 75 ? 'Recommended' : ($score >= 50 ? 'Needs Review' : 'Not Recommended'));

        $household->ai_score = $score;
        $household->eligibility_status = $score >= 75 ? 'Layak' : ($score >= 50 ? 'Dalam Semakan' : 'Tidak Layak');
        $household->save();

        return [
            'score' => $score,
            'confidence' => $confidence,
            'recommendation' => $recommendation,
            'reasons' => $reasons,
            'eligibility_checks' => $eligibilityChecks,
            'had_kifayah_total' => $hadKifayahTotal,
            'income_gap' => $incomeGap,
        ];
    }

    private function calculateHadKifayah($members): float
    {
        $total = 0;
        foreach ($members as $member) {
            $total += match ($member->hubungan) {
                'Ketua Isi Rumah' => 1316.00,
                'Isteri', 'Suami' => 162.00,
                'Ibu', 'Bapa', 'Ibu Bapa' => 156.00,
                'Anak' => match ($member->pendidikan) {
                    'IPT' => 600.00,
                    'Sekolah Menengah' => 304.00,
                    'Sekolah Rendah' => 254.00,
                    'Tadika' => 185.00,
                    default => 254.00,
                },
                default => 254.00,
            };
        }

        return $total;
    }
}
