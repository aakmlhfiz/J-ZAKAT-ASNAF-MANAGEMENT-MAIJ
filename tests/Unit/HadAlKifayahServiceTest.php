<?php

namespace Tests\Unit;

use App\Services\HadAlKifayahService;
use Database\Seeders\HadKifayahSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HadAlKifayahServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HadKifayahSettingSeeder::class);
    }

    public function test_calculate_returns_layak_when_income_below_had_kifayah(): void
    {
        $result = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 500,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 1,
            'n_remaja' => 2,
        ]);

        $this->assertGreaterThan(0, $result['had_kifayah_total']);
        $this->assertLessThan(0, $result['baki']);
        $this->assertEquals(HadAlKifayahService::STATUS_LAYAK, $result['status']);
        $this->assertArrayHasKey('breakdown', $result);
        $this->assertIsArray($result['reasoning']);
        $this->assertGreaterThanOrEqual(50, $result['confidence']);
    }

    public function test_calculate_returns_tidak_layak_when_income_above_had_kifayah(): void
    {
        $result = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 10000,
            'n_isteri_suami' => 0,
            'n_ibu_bapa' => 0,
            'n_remaja' => 0,
        ]);

        $this->assertGreaterThan(0, $result['had_kifayah_total']);
        $this->assertGreaterThan(0, $result['baki']);
        $this->assertEquals(HadAlKifayahService::STATUS_TIDAK_LAYAK, $result['status']);
    }

    public function test_calculate_handles_defaults_for_missing_inputs(): void
    {
        $result = HadAlKifayahService::calculate([]);

        $this->assertArrayHasKey('pendapatan_total', $result);
        $this->assertArrayHasKey('baki', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(0, $result['pendapatan_total']);
    }

    public function test_calculate_returns_consistent_results(): void
    {
        $first = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 1500,
            'n_isteri_suami' => 1,
            'n_kanak_tadika' => 1,
            'sewa' => 300,
        ]);

        $second = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 1500,
            'n_isteri_suami' => 1,
            'n_kanak_tadika' => 1,
            'sewa' => 300,
        ]);

        $this->assertEquals($first['had_kifayah_total'], $second['had_kifayah_total']);
        $this->assertEquals($first['status'], $second['status']);
        $this->assertEquals($first['confidence'], $second['confidence']);
    }

    public function test_calculate_includes_household_composition_in_breakdown(): void
    {
        $result = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 2000,
            'n_isteri_suami' => 1,
            'n_ipt' => 2,
            'n_kanak_tadika' => 3,
        ]);

        $labels = array_map(fn ($item) => $item['label'], $result['breakdown']);
        $this->assertStringContainsString('Isteri / Suami', implode(' ', $labels));
        $this->assertStringContainsString('IPT', implode(' ', $labels));
        $this->assertStringContainsString('Tadika', implode(' ', $labels));
    }

    public function test_calculate_applies_house_rental_when_sewa_selected(): void
    {
        $withoutSewa = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 1000,
            'perumahan' => 'sendiri',
        ]);

        $withSewa = HadAlKifayahService::calculate([
            'pendapatan_pemohon' => 1000,
            'perumahan' => 'sewa',
        ]);

        $this->assertGreaterThanOrEqual($withoutSewa['had_kifayah_total'], $withSewa['had_kifayah_total']);
    }
}
