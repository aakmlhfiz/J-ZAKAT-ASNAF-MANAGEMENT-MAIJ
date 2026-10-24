<?php

namespace App\Services\Ocr\DocumentIntelligence;

/**
 * Financial consistency audit for the MAIJ form's "MAKLUMAT PENDAPATAN &
 * PERBELANJAAN" section.
 *
 * Given an ocr_result it sums the detected income/expense rows and cross-checks
 * them against the declared JUMLAH (total) rows, checks the income/expense
 * balance, and verifies the main income against the applicant's declared
 * pendapatan. Key-agnostic so it works for both the local template engine and
 * the vision engine.
 */
class FinancialAudit
{
    public const INCOME_TOTAL = 'jumlah_pendapatan';

    public const EXPENSE_TOTAL = 'jumlah_perbelanjaan';

    /** Tolerated rounding difference (RM) when comparing totals to row sums. */
    private const TOLERANCE = 1.00;

    /**
     * @return array<string, mixed>
     */
    public function audit(array $ocrResult): array
    {
        $income = $ocrResult['income'] ?? [];
        $expenses = $ocrResult['expenses'] ?? [];
        $applicant = $ocrResult['applicant'] ?? [];

        $incomeRows = $this->rows($income, self::INCOME_TOTAL);
        $expenseRows = $this->rows($expenses, self::EXPENSE_TOTAL);

        $incomeSum = $this->sum($incomeRows);
        $expenseSum = $this->sum($expenseRows);

        $incomeTotal = $this->total($income, self::INCOME_TOTAL);
        $expenseTotal = $this->total($expenses, self::EXPENSE_TOTAL);

        $checks = [
            $this->checkTotal('Jumlah Pendapatan', $incomeTotal, $incomeSum, count($incomeRows)),
            $this->checkTotal('Jumlah Perbelanjaan', $expenseTotal, $expenseSum, count($expenseRows)),
            $this->checkBalance($incomeSum, $expenseSum),
            $this->checkMainIncome($applicant, $income),
        ];

        $hasFail = collect($checks)->contains(fn ($c) => $c['status'] === 'fail');
        $hasWarn = collect($checks)->contains(fn ($c) => $c['status'] === 'warn');

        $overall = $hasFail ? 'fail' : ($hasWarn ? 'warn' : 'pass');

        return [
            'income_sum' => $incomeSum,
            'expense_sum' => $expenseSum,
            'income_total' => $incomeTotal,
            'expense_total' => $expenseTotal,
            'checks' => $checks,
            'overall' => $overall,
            'needs_review' => $overall !== 'pass',
        ];
    }

    /**
     * All row entries except the given total key.
     */
    private function rows(array $group, string $totalKey): array
    {
        return array_filter($group, fn ($key) => $key !== $totalKey, ARRAY_FILTER_USE_KEY);
    }

    private function sum(array $rows): float
    {
        $sum = 0.0;
        foreach ($rows as $entry) {
            $value = is_array($entry) ? ($entry['value'] ?? null) : $entry;
            if (is_numeric($value)) {
                $sum += (float) $value;
            }
        }

        return round($sum, 2);
    }

    private function total(array $group, string $totalKey): ?float
    {
        $entry = $group[$totalKey] ?? null;
        $value = is_array($entry) ? ($entry['value'] ?? null) : $entry;

        return is_numeric($value) ? (float) $value : null;
    }

    private function checkTotal(string $label, ?float $total, float $sum, int $rowCount): array
    {
        if ($total === null && $sum > 0) {
            return [
                'status' => 'warn',
                'message' => "{$label} tidak dikesan, tetapi butiran berjumlah RM ".number_format($sum, 2).'.',
                'expected' => $sum,
                'actual' => null,
            ];
        }

        if ($total === null && $sum == 0) {
            return [
                'status' => 'skip',
                'message' => "Tiada data untuk semakan {$label}.",
                'expected' => null,
                'actual' => null,
            ];
        }

        if ($total !== null && $sum == 0 && $rowCount === 0) {
            return [
                'status' => 'warn',
                'message' => "{$label} dikesan (RM ".number_format($total, 2).') tetapi butiran tidak lengkap.',
                'expected' => $total,
                'actual' => null,
            ];
        }

        $diff = abs($total - $sum);
        if ($diff <= self::TOLERANCE) {
            return [
                'status' => 'pass',
                'message' => "{$label} sepadan dengan butiran (RM ".number_format($sum, 2).').',
                'expected' => $total,
                'actual' => $sum,
            ];
        }

        return [
            'status' => 'fail',
            'message' => "{$label} (RM ".number_format($total, 2).') tidak sepadan dengan jumlah butiran (RM '.number_format($sum, 2).').',
            'expected' => $total,
            'actual' => $sum,
        ];
    }

    private function checkBalance(float $incomeSum, float $expenseSum): array
    {
        if ($incomeSum <= 0 && $expenseSum <= 0) {
            return ['status' => 'skip', 'message' => 'Tiada data untuk semakan baki.', 'expected' => null, 'actual' => null];
        }

        if ($expenseSum > 0 && $incomeSum > 0 && $expenseSum > ($incomeSum * 1.2)) {
            return [
                'status' => 'warn',
                'message' => 'Perbelanjaan melebihi pendapatan (belanja RM '.number_format($expenseSum, 2).' vs pendapatan RM '.number_format($incomeSum, 2).').',
                'expected' => $incomeSum,
                'actual' => $expenseSum,
            ];
        }

        return [
            'status' => 'pass',
            'message' => 'Pendapatan '.number_format($incomeSum, 2).' / Perbelanjaan '.number_format($expenseSum, 2).' — tiada ketidakseimbangan ketara.',
            'expected' => $incomeSum,
            'actual' => $expenseSum,
        ];
    }

    private function checkMainIncome(array $applicant, array $income): array
    {
        $main = is_numeric($applicant['pendapatan']['value'] ?? null)
            ? (float) $applicant['pendapatan']['value']
            : null;

        $diriValue = null;
        foreach ($income as $key => $entry) {
            if (mb_strpos($key, 'diri') !== false) {
                $value = is_array($entry) ? ($entry['value'] ?? null) : $entry;
                if (is_numeric($value)) {
                    $diriValue = (float) $value;
                }
                break;
            }
        }

        if ($main === null || $diriValue === null) {
            return ['status' => 'skip', 'message' => 'Pendapatan utama tidak dapat disemak.', 'expected' => null, 'actual' => null];
        }

        if (abs($main - $diriValue) > self::TOLERANCE) {
            return [
                'status' => 'warn',
                'message' => 'Pendapatan pemohon (RM '.number_format($main, 2).') berbeza dengan pendapatan diri (RM '.number_format($diriValue, 2).').',
                'expected' => $main,
                'actual' => $diriValue,
            ];
        }

        return ['status' => 'pass', 'message' => 'Pendapatan utama sepadan dengan butiran diri.', 'expected' => $main, 'actual' => $diriValue];
    }
}
