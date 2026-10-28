<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\Household;

class HouseholdInsightService
{
    public function generate(Household $household): array
    {
        $insights = [];
        $members = $household->members;
        $assistanceHistories = $household->assistanceHistories;
        $totalIncome = (float) $members->sum('pendapatan_bulanan');
        $totalExpenses = (float) $household->total_expenses;

        $jenisBantuanDiterima = $assistanceHistories
            ->where('status', 'approved')
            ->pluck('jenis_bantuan')
            ->unique()
            ->toArray();

        $allJenis = array_keys(AssistanceHistory::jenisBantuanOptions());
        $jenisTidakDiterima = array_diff($allJenis, $jenisBantuanDiterima);

        if (! empty($jenisTidakDiterima) && ! empty($jenisBantuanDiterima)) {
            $insights[] = [
                'tone' => 'info',
                'icon' => 'fa-lightbulb',
                'title' => __('household.insights.missing_assistance_title'),
                'description' => __('household.insights.missing_assistance_description', [
                    'received' => implode(', ', $jenisBantuanDiterima),
                    'missing' => implode(', ', array_slice($jenisTidakDiterima, 0, 3)),
                ]),
            ];
        }

        $dialysisMember = $members->first(fn ($m) => $m->assistanceHistories()
            ->where('jenis_bantuan', 'Dialisis')
            ->where('status', 'approved')
            ->exists());

        if ($dialysisMember) {
            $insights[] = [
                'tone' => 'warning',
                'icon' => 'fa-heart-pulse',
                'title' => __('household.insights.dialysis_title'),
                'description' => __('household.insights.dialysis_description', ['name' => $dialysisMember->nama]),
            ];
        }

        $iptMember = $members->first(fn ($m) => $m->assistanceHistories()
            ->where('jenis_bantuan', 'IPT')
            ->where('status', 'approved')
            ->exists());

        if ($iptMember) {
            $insights[] = [
                'tone' => 'primary',
                'icon' => 'fa-graduation-cap',
                'title' => __('household.insights.ipt_title'),
                'description' => __('household.insights.ipt_description', ['name' => $iptMember->nama]),
            ];
        }

        if ($totalExpenses > $totalIncome && $totalIncome > 0) {
            $defisit = $totalExpenses - $totalIncome;
            $insights[] = [
                'tone' => 'danger',
                'icon' => 'fa-circle-exclamation',
                'title' => __('household.insights.expenses_title'),
                'description' => __('household.insights.expenses_description', [
                    'expenses' => number_format($totalExpenses, 2),
                    'income' => number_format($totalIncome, 2),
                    'deficit' => number_format($defisit, 2),
                ]),
            ];
        }

        $singleMother = $members->filter(fn ($m) => in_array($m->hubungan, ['Ibu', 'Ibu Tunggal']));
        if ($singleMother->isNotEmpty() && $members->where('hubungan', 'Suami')->isEmpty() && $members->where('hubungan', 'Ketua Isi Rumah')->isEmpty() === false) {
            $insights[] = [
                'tone' => 'warning',
                'icon' => 'fa-person',
                'title' => __('household.insights.single_mother_title'),
                'description' => __('household.insights.single_mother_description'),
            ];
        }

        $elderlyCount = $members->filter(fn ($m) => $m->umur >= 60)->count();
        if ($elderlyCount > 0) {
            $insights[] = [
                'tone' => 'info',
                'icon' => 'fa-user-clock',
                'title' => __('household.insights.elderly_title'),
                'description' => __('household.insights.elderly_description', ['count' => $elderlyCount]),
            ];
        }

        $okuCount = $members->filter(fn ($m) => str_contains(strtolower($m->status_kesihatan ?? ''), 'oku'))->count();
        if ($okuCount > 0) {
            $insights[] = [
                'tone' => 'warning',
                'icon' => 'fa-wheelchair',
                'title' => __('household.insights.oku_title'),
                'description' => __('household.insights.oku_description', ['count' => $okuCount]),
            ];
        }

        if ($household->ai_score >= 90) {
            $insights[] = [
                'tone' => 'success',
                'icon' => 'fa-star',
                'title' => __('household.insights.priority_title'),
                'description' => __('household.insights.priority_description', ['score' => round($household->ai_score)]),
            ];
        }

        $monthlyAssistanceEnabled = $assistanceHistories
            ->where('frequency', 'monthly')
            ->where('status', 'approved')
            ->count();

        if ($monthlyAssistanceEnabled > 0) {
            $totalMonthly = $assistanceHistories
                ->where('frequency', 'monthly')
                ->where('status', 'approved')
                ->sum('amount');
            $insights[] = [
                'tone' => 'primary',
                'icon' => 'fa-money-bill-wave',
                'title' => __('household.insights.monthly_title'),
                'description' => __('household.insights.monthly_description', [
                    'count' => $monthlyAssistanceEnabled,
                    'amount' => number_format($totalMonthly, 2),
                ]),
            ];
        }

        return $insights;
    }
}
