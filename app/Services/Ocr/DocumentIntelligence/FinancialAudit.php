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
            $this->checkTotal(__('ocr.audit.income_total'), $incomeTotal, $incomeSum, count($incomeRows)),
            $this->checkTotal(__('ocr.audit.expense_total'), $expenseTotal, $expenseSum, count($expenseRows)),
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
                'message' => __('ocr.audit.total_not_detected', ['label' => $label, 'sum' => number_format($sum, 2)]),
                'expected' => $sum,
                'actual' => null,
            ];
        }

        if ($total === null && $sum == 0) {
            return [
                'status' => 'skip',
                'message' => __('ocr.audit.no_data_total', ['label' => $label]),
                'expected' => null,
                'actual' => null,
            ];
        }

        if ($total !== null && $sum == 0 && $rowCount === 0) {
            return [
                'status' => 'warn',
                'message' => __('ocr.audit.total_incomplete', ['label' => $label, 'total' => number_format($total, 2)]),
                'expected' => $total,
                'actual' => null,
            ];
        }

        $diff = abs($total - $sum);
        if ($diff <= self::TOLERANCE) {
            return [
                'status' => 'pass',
                'message' => __('ocr.audit.total_matches', ['label' => $label, 'sum' => number_format($sum, 2)]),
                'expected' => $total,
                'actual' => $sum,
            ];
        }

        return [
            'status' => 'fail',
            'message' => __('ocr.audit.total_mismatch', ['label' => $label, 'total' => number_format($total, 2), 'sum' => number_format($sum, 2)]),
            'expected' => $total,
            'actual' => $sum,
        ];
    }

    private function checkBalance(float $incomeSum, float $expenseSum): array
    {
        if ($incomeSum <= 0 && $expenseSum <= 0) {
            return ['status' => 'skip', 'message' => __('ocr.audit.no_data_balance'), 'expected' => null, 'actual' => null];
        }

        if ($expenseSum > 0 && $incomeSum > 0 && $expenseSum > ($incomeSum * 1.2)) {
            return [
                'status' => 'warn',
                'message' => __('ocr.audit.expense_exceeds_income', ['expense' => number_format($expenseSum, 2), 'income' => number_format($incomeSum, 2)]),
                'expected' => $incomeSum,
                'actual' => $expenseSum,
            ];
        }

        return [
            'status' => 'pass',
            'message' => __('ocr.audit.balance_ok', ['income' => number_format($incomeSum, 2), 'expense' => number_format($expenseSum, 2)]),
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
            return ['status' => 'skip', 'message' => __('ocr.audit.main_income_unverified'), 'expected' => null, 'actual' => null];
        }

        if (abs($main - $diriValue) > self::TOLERANCE) {
            return [
                'status' => 'warn',
                'message' => __('ocr.audit.main_income_mismatch', ['main' => number_format($main, 2), 'self' => number_format($diriValue, 2)]),
                'expected' => $main,
                'actual' => $diriValue,
            ];
        }

        return ['status' => 'pass', 'message' => __('ocr.audit.main_income_matches'), 'expected' => $main, 'actual' => $diriValue];
    }
}
