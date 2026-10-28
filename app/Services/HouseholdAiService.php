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
            $reasons[] = __('household.ai_service.reason_income_below');
            $eligibilityChecks[] = [
                'label' => __('household.ai_service.check_income_below'),
                'passed' => true,
                'detail' => __('household.ai_service.detail_income_below', [
                    'income' => number_format($totalIncome, 2),
                    'kifayah' => number_format($hadKifayahTotal, 2),
                ]),
            ];
        } else {
            $score -= 10;
            $reasons[] = __('household.ai_service.reason_income_above');
            $eligibilityChecks[] = [
                'label' => __('household.ai_service.check_income_below'),
                'passed' => false,
                'detail' => __('household.ai_service.detail_income_above', [
                    'income' => number_format($totalIncome, 2),
                    'kifayah' => number_format($hadKifayahTotal, 2),
                ]),
            ];
        }

        if ($dependentsCount >= 5) {
            $score += 10;
            $reasons[] = __('household.ai_service.reason_dependents_high');
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_dependents'), 'passed' => true, 'detail' => __('household.ai_service.detail_dependents', ['count' => $dependentsCount])];
        } elseif ($dependentsCount >= 3) {
            $score += 5;
            $reasons[] = __('household.ai_service.reason_dependents_mid');
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_dependents'), 'passed' => true, 'detail' => __('household.ai_service.detail_dependents', ['count' => $dependentsCount])];
        } else {
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_dependents'), 'passed' => true, 'detail' => __('household.ai_service.detail_dependents', ['count' => $dependentsCount])];
        }

        if ($totalExpenses > $totalIncome) {
            $score += 10;
            $reasons[] = __('household.ai_service.reason_expenses_exceed');
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_expenses'), 'passed' => true, 'detail' => __('household.ai_service.detail_deficit', ['amount' => number_format($totalExpenses - $totalIncome, 2)])];
        }

        if ($members->contains(fn ($m) => str_contains(strtolower($m->status_kesihatan ?? ''), 'kronik'))) {
            $score += 8;
            $reasons[] = __('household.ai_service.reason_chronic');
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_chronic'), 'passed' => true, 'detail' => __('household.ai_service.detail_chronic')];
        }

        if (str_contains(strtolower($household->alamat ?? ''), 'sewa')) {
            $score += 5;
            $reasons[] = __('household.ai_service.reason_rental');
        }

        $iptCount = $members->where('pendidikan', 'IPT')->count();
        if ($iptCount > 0) {
            $score += 8;
            $reasons[] = __('household.ai_service.reason_ipt', ['count' => $iptCount]);
            $eligibilityChecks[] = ['label' => __('household.ai_service.check_ipt'), 'passed' => true, 'detail' => __('household.ai_service.detail_ipt', ['count' => $iptCount])];
        }

        $score += min(10, $assistanceCount * 2);
        if ($assistanceCount > 0) {
            $reasons[] = __('household.ai_service.reason_history');
        }

        $eligibilityChecks = array_merge($eligibilityChecks, [
            ['label' => __('household.ai_service.check_muslim'), 'passed' => true, 'detail' => __('household.ai_service.detail_muslim')],
            ['label' => __('household.ai_service.check_citizen'), 'passed' => true, 'detail' => __('household.ai_service.detail_citizen')],
            ['label' => __('household.ai_service.check_johor'), 'passed' => true, 'detail' => __('household.ai_service.detail_johor')],
            ['label' => __('household.ai_service.check_documents'), 'passed' => $assistanceCount > 0, 'detail' => $assistanceCount > 0 ? __('household.ai_service.detail_documents_ok') : __('household.ai_service.detail_documents_pending')],
            ['label' => __('household.ai_service.check_fraud'), 'passed' => true, 'detail' => __('household.ai_service.detail_fraud')],
            ['label' => __('household.ai_service.check_duplicate'), 'passed' => true, 'detail' => __('household.ai_service.detail_duplicate')],
            ['label' => __('household.ai_service.check_blacklist'), 'passed' => true, 'detail' => __('household.ai_service.detail_blacklist')],
        ]);

        $score = max(0, min(99, $score));

        $confidence = $score >= 90 ? 98 : ($score >= 75 ? 85 : ($score >= 50 ? 70 : 50));
        $recommendation = $score >= 90
            ? __('household.ai.recommendation.highly_recommended')
            : ($score >= 75
                ? __('household.ai.recommendation.recommended')
                : ($score >= 50 ? __('household.ai.recommendation.needs_review') : __('household.ai.recommendation.not_recommended')));

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
