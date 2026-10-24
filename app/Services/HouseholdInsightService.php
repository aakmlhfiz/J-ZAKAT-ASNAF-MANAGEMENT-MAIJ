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
                'title' => 'Bantuan Belum Diterima',
                'description' => 'Isi rumah ini menerima bantuan '.implode(', ', $jenisBantuanDiterima).' tetapi belum pernah menerima bantuan '.implode(', ', array_slice($jenisTidakDiterima, 0, 3)).'.',
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
                'title' => 'Pesakit Dialisis',
                'description' => $dialysisMember->nama.' sedang menerima bantuan dialisis. Perlu pemantauan berkala.',
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
                'title' => 'Bantuan IPT Aktif',
                'description' => $iptMember->nama.' sedang menerima bantuan IPT. Semak status pengajian setiap semester.',
            ];
        }

        if ($totalExpenses > $totalIncome && $totalIncome > 0) {
            $defisit = $totalExpenses - $totalIncome;
            $insights[] = [
                'tone' => 'danger',
                'icon' => 'fa-circle-exclamation',
                'title' => 'Perbelanjaan Melebihi Pendapatan',
                'description' => 'Perbelanjaan bulanan (RM '.number_format($totalExpenses, 2).') melebihi pendapatan (RM '.number_format($totalIncome, 2).') sebanyak RM '.number_format($defisit, 2).'. Isi rumah ini memerlukan bantuan segera.',
            ];
        }

        $singleMother = $members->filter(fn ($m) => in_array($m->hubungan, ['Ibu', 'Ibu Tunggal']));
        if ($singleMother->isNotEmpty() && $members->where('hubungan', 'Suami')->isEmpty() && $members->where('hubungan', 'Ketua Isi Rumah')->isEmpty() === false) {
            $insights[] = [
                'tone' => 'warning',
                'icon' => 'fa-person',
                'title' => 'Ibu Tunggal',
                'description' => 'Isi rumah ini diketuai oleh ibu tunggal. Keutamaan tinggi untuk bantuan sara hidup.',
            ];
        }

        $elderlyCount = $members->filter(fn ($m) => $m->umur >= 60)->count();
        if ($elderlyCount > 0) {
            $insights[] = [
                'tone' => 'info',
                'icon' => 'fa-user-clock',
                'title' => 'Warga Emas',
                'description' => $elderlyCount.' orang warga emas dalam isi rumah ini. Pertimbangkan bantuan warga emas.',
            ];
        }

        $okuCount = $members->filter(fn ($m) => str_contains(strtolower($m->status_kesihatan ?? ''), 'oku'))->count();
        if ($okuCount > 0) {
            $insights[] = [
                'tone' => 'warning',
                'icon' => 'fa-wheelchair',
                'title' => 'Orang Kurang Upaya (OKU)',
                'description' => $okuCount.' orang OKU dalam isi rumah. Layak untuk bantuan khas OKU.',
            ];
        }

        if ($household->ai_score >= 90) {
            $insights[] = [
                'tone' => 'success',
                'icon' => 'fa-star',
                'title' => 'Isi Rumah Keutamaan',
                'description' => 'Skor AI tinggi ('.round($household->ai_score).'%). Isi rumah ini disyorkan sebagai keutamaan untuk bantuan.',
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
                'title' => 'Bantuan Bulanan Aktif',
                'description' => $monthlyAssistanceEnabled.' bantuan bulanan aktif berjumlah RM '.number_format($totalMonthly, 2).'/bulan.',
            ];
        }

        return $insights;
    }
}
