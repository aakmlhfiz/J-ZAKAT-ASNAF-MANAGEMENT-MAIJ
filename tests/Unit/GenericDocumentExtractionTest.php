<?php

namespace Tests\Unit;

use App\Services\Document\DocumentConfidenceResolver;
use App\Services\Document\Extractors\BankStatementExtractor;
use App\Services\Document\Extractors\MyKadExtractor;
use App\Services\Document\Extractors\SalarySlipExtractor;
use App\Services\Document\Extractors\UtilityBillExtractor;
use Tests\TestCase;

class GenericDocumentExtractionTest extends TestCase
{
    private function context(): array
    {
        return [
            'confidence_resolver' => new DocumentConfidenceResolver,
            'mime_type' => 'image/png',
            'page_info' => ['page_count' => 1, 'has_text_layer' => false],
        ];
    }

    public function test_mykad_extracts_identity_fields_without_inventing_confidence(): void
    {
        $result = (new MyKadExtractor)->extract(<<<'TEXT'
Nama Penuh: ALI BIN ABU
No. Kad Pengenalan: 900101-01-1234
Alamat: 12 Jalan Mawar
Taman Seri Pulai
Poskod: 80100
Negeri: Johor
TEXT, $this->context());

        $this->assertSame('ALI BIN ABU', $result['fields']['full_name']['value']);
        $this->assertSame('900101011234', $result['fields']['ic_number']['value']);
        $this->assertSame('80100', $result['fields']['postcode']['value']);
        $this->assertSame('Johor', $result['fields']['state']['value']);
        $this->assertNull($result['fields']['full_name']['confidence']);
        $this->assertNull($result['fields']['full_name']['confidence_source']);
    }

    public function test_salary_slip_extracts_amounts_and_flags_arithmetic_mismatch(): void
    {
        $result = (new SalarySlipExtractor)->extract(<<<'TEXT'
Nama Pekerja: SITI AMINAH
Majikan: SYARIKAT CONTOH SDN BHD
Bulan Gaji: Januari 2026
Gaji Pokok: RM 2,500.00
Elaun: RM 300.00
Gaji Kasar: RM 2,800.00
KWSP: RM 280.00
PERKESO: RM 28.00
Gaji Bersih: RM 2,492.00
TEXT, $this->context());

        $this->assertSame('2500.00', $result['fields']['basic_salary']['value']);
        $this->assertSame('2800.00', $result['fields']['gross_salary']['value']);
        $this->assertSame('2492.00', $result['fields']['net_salary']['value']);
        $this->assertSame([], $result['warnings']);
    }

    public function test_bank_statement_never_promotes_credits_to_income(): void
    {
        $result = (new BankStatementExtractor)->extract(<<<'TEXT'
Nama Pemegang Akaun: ALI BIN ABU
Nama Bank: BANK CONTOH
Tempoh Penyata: 01/01/2026 - 31/01/2026
Baki Permulaan: RM 1,000.00
Baki Penutup: RM 1,500.00
01/01/2026 GAJI CR 2,500.00
05/01/2026 BAYARAN UTILITI DR 200.00
TEXT, $this->context());

        $this->assertSame('1000.00', $result['fields']['opening_balance']['value']);
        $this->assertSame('1500.00', $result['fields']['closing_balance']['value']);
        $this->assertCount(2, $result['meta']['transactions']);
        $this->assertSame('credit', $result['meta']['transactions'][0]['type']);
        $this->assertStringContainsString('pendapatan', $result['warnings'][0]);
    }

    public function test_utility_bill_extracts_total_and_due_date(): void
    {
        $result = (new UtilityBillExtractor)->extract(<<<'TEXT'
Tenaga Nasional Berhad
Nama Pelanggan: ALI BIN ABU
No. Akaun: 123456789
Tempoh Bil: Januari 2026
Jumlah Perlu Dibayar: RM 185.50
Tarikh Akhir Bayar: 28/02/2026
TEXT, $this->context());

        $this->assertSame('185.50', $result['fields']['total_amount']['value']);
        $this->assertSame('2026-02-28', $result['fields']['due_date']['value']);
        $this->assertSame('Tenaga Nasional', $result['fields']['provider']['value']);
    }
}
