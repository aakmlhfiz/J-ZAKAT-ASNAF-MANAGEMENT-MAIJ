<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\FinancialAudit;
use App\Services\Ocr\DocumentIntelligence\MaiFormTemplate;
use Tests\TestCase;

class FinancialAuditTest extends TestCase
{
    private FinancialAudit $audit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->audit = new FinancialAudit;
    }

    private function row(?string $value): array
    {
        return ['value' => $value, 'source' => 'x', 'needs_review' => $value === null];
    }

    public function test_totals_matching_rows_pass(): void
    {
        $result = $this->audit->audit([
            'income' => [
                'diri' => $this->row('1500.00'),
                'sumbangan_anak' => $this->row('200.00'),
                FinancialAudit::INCOME_TOTAL => $this->row('1700.00'),
            ],
            'expenses' => [
                'makan_minum' => $this->row('600.00'),
                FinancialAudit::EXPENSE_TOTAL => $this->row('600.00'),
            ],
            'applicant' => ['pendapatan' => $this->row('1500.00')],
        ]);

        $this->assertSame('pass', $result['overall']);
        $this->assertFalse($result['needs_review']);
        $this->assertSame(1700.0, $result['income_sum']);
        $this->assertSame(600.0, $result['expense_sum']);
    }

    public function test_total_mismatch_fails(): void
    {
        $result = $this->audit->audit([
            'income' => [
                'diri' => $this->row('1500.00'),
                FinancialAudit::INCOME_TOTAL => $this->row('1800.00'),
            ],
            'expenses' => [],
            'applicant' => [],
        ]);

        $this->assertSame('fail', $result['overall']);
        $this->assertTrue($result['needs_review']);

        $check = collect($result['checks'])->first(fn ($c) => $c['status'] === 'fail');
        $this->assertNotNull($check);
        $this->assertStringContainsString('tidak sepadan', $check['message']);
    }

    public function test_missing_total_warns(): void
    {
        $result = $this->audit->audit([
            'income' => ['diri' => $this->row('1500.00')],
            'expenses' => [],
            'applicant' => [],
        ]);

        $this->assertSame('warn', $result['overall']);
        $this->assertTrue($result['needs_review']);
    }

    public function test_total_without_breakdown_warns(): void
    {
        $result = $this->audit->audit([
            'income' => [FinancialAudit::INCOME_TOTAL => $this->row('1700.00')],
            'expenses' => [],
            'applicant' => [],
        ]);

        $check = collect($result['checks'])->first(fn ($c) => $c['status'] === 'warn');
        $this->assertNotNull($check);
        $this->assertStringContainsString('butiran tidak lengkap', $check['message']);
    }

    public function test_expenses_exceeding_income_warns(): void
    {
        $result = $this->audit->audit([
            'income' => ['diri' => $this->row('1000.00')],
            'expenses' => ['makan_minum' => $this->row('1500.00')],
            'applicant' => [],
        ]);

        $this->assertSame('warn', $result['overall']);
        $check = collect($result['checks'])->first(fn ($c) => $c['status'] === 'warn' && str_contains($c['message'], 'Perbelanjaan melebihi'));
        $this->assertNotNull($check);
    }

    public function test_main_income_divergence_warns(): void
    {
        $result = $this->audit->audit([
            'income' => ['pendapatan_diri' => $this->row('1200.00')],
            'expenses' => [],
            'applicant' => ['pendapatan' => $this->row('900.00')],
        ]);

        $check = collect($result['checks'])->first(fn ($c) => str_contains($c['message'], 'Pendapatan pemohon'));
        $this->assertNotNull($check);
        $this->assertSame('warn', $check['status']);
    }

    public function test_no_data_skips_without_failing(): void
    {
        $result = $this->audit->audit([
            'income' => [],
            'expenses' => [],
            'applicant' => [],
        ]);

        $this->assertSame('pass', $result['overall']);
        $this->assertFalse($result['needs_review']);
    }

    public function test_template_rows_include_jumlah_totals(): void
    {
        $incomeKeys = array_column(MaiFormTemplate::incomeRows(), 'key');
        $expenseKeys = array_column(MaiFormTemplate::expenseRows(), 'key');

        $this->assertContains('jumlah_pendapatan', $incomeKeys);
        $this->assertContains('jumlah_perbelanjaan', $expenseKeys);
    }

    public function test_income_and_expense_amount_columns_are_separate(): void
    {
        $incomeZones = array_column(MaiFormTemplate::incomeRows(), 'amount_zone');
        $expenseZones = array_column(MaiFormTemplate::expenseRows(), 'amount_zone');

        $incomeX = collect($incomeZones)->filter(fn ($z) => $z !== null)->map(fn ($z) => $z[0])->min();
        $expenseX = collect($expenseZones)->filter(fn ($z) => $z !== null)->map(fn ($z) => $z[0])->min();

        // Income amounts live left of expense amounts (separate columns).
        $this->assertLessThan($expenseX, $incomeX);
    }
}
