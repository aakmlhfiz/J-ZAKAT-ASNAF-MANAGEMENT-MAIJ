<?php

namespace Database\Factories;

use App\Models\ProfilAsnaf;
use App\Services\HadAlKifayahService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfilAsnaf>
 */
class ProfilAsnafFactory extends Factory
{
    protected $model = ProfilAsnaf::class;

    public function definition(): array
    {
        $nIs = fake()->numberBetween(0, 1);
        $nIbuBapa = fake()->numberBetween(0, 2);
        $nRemaja = fake()->numberBetween(0, 3);
        $nKanakRendah = fake()->numberBetween(0, 2);
        $nKanakTadika = fake()->numberBetween(0, 1);

        $pendapatan = fake()->numberBetween(800, 4500);
        $sewa = fake()->randomElement([0, 200, 300, 400, 500]);
        $utiliti = fake()->numberBetween(80, 250);

        $hadKifayah = (1316.00)
            + ($nIs * 162.00)
            + ($nIbuBapa * 156.00)
            + ($nRemaja * 304.00)
            + ($nKanakRendah * 254.00)
            + ($nKanakTadika * 185.00)
            + $sewa
            + $utiliti;

        $status = $pendapatan < $hadKifayah
            ? HadAlKifayahService::STATUS_LAYAK
            : HadAlKifayahService::STATUS_TIDAK_LAYAK;

        return [
            'nama' => fake()->name(),
            'ic' => fake()->unique()->numerify('############'),
            'ic_ketua_rumah' => fake()->numerify('############'),
            'hubungan' => fake()->randomElement(['Ketua Keluarga', 'Isteri', 'Suami', 'Anak', 'Ibu', 'Bapa']),
            'telefon' => '01'.fake()->numerify('#########'),
            'alamat' => fake()->streetAddress().', '.fake()->city(),
            'pendapatan' => $pendapatan,
            'bilangan_tanggungan' => $nIs + $nIbuBapa + $nRemaja + $nKanakRendah + $nKanakTadika,
            'sewa' => $sewa,
            'utiliti' => $utiliti,
            'n_isteri_suami' => $nIs,
            'n_ibu_bapa' => $nIbuBapa,
            'n_dewasa_tidak_bekerja' => 0,
            'n_dewasa_bekerja_tanggung' => 0,
            'n_ipt' => 0,
            'n_remaja' => $nRemaja,
            'n_kanak_rendah' => $nKanakRendah,
            'n_kanak_tadika' => $nKanakTadika,
            'perumahan' => $sewa > 0 ? 'sewa' : 'sendiri',
            'pengangkutan_input' => fake()->numberBetween(50, 200),
            'perubatan_input' => fake()->numberBetween(0, 150),
            'had_kifayah' => $hadKifayah,
            'ai_score' => fake()->numberBetween(60, 95),
            'status_kelayakan' => $status,
        ];
    }

    public function layak(): static
    {
        return $this->state(fn () => [
            'pendapatan' => 1200,
            'bilangan_tanggungan' => 4,
            'sewa' => 0,
            'utiliti' => 100,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 0,
            'n_remaja' => 2,
            'n_kanak_rendah' => 0,
            'n_kanak_tadika' => 1,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 80,
            'perubatan_input' => 50,
            'had_kifayah' => 1316 + 162 + (2 * 304) + 185,
            'ai_score' => 88,
            'status_kelayakan' => HadAlKifayahService::STATUS_LAYAK,
        ]);
    }

    public function tidakLayak(): static
    {
        return $this->state(fn () => [
            'pendapatan' => 5000,
            'bilangan_tanggungan' => 2,
            'sewa' => 0,
            'utiliti' => 150,
            'n_isteri_suami' => 1,
            'n_ibu_bapa' => 0,
            'n_remaja' => 1,
            'n_kanak_rendah' => 0,
            'n_kanak_tadika' => 0,
            'perumahan' => 'sendiri',
            'pengangkutan_input' => 200,
            'perubatan_input' => 100,
            'had_kifayah' => 1316 + 162 + 304,
            'ai_score' => 75,
            'status_kelayakan' => HadAlKifayahService::STATUS_TIDAK_LAYAK,
        ]);
    }
}
