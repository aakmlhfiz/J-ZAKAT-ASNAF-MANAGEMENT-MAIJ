<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HadKifayahSettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['kategori' => 'Ketua Keluarga', 'subkategori' => null,                                            'nilai' => 1316.00],
            ['kategori' => 'Dewasa',          'subkategori' => 'Isteri / Suami',                                 'nilai' => 162.00],
            ['kategori' => 'Dewasa',          'subkategori' => 'Ibu Bapa Tanggungan',                            'nilai' => 156.00],
            ['kategori' => 'Dewasa',          'subkategori' => 'Dewasa Tidak Bekerja',                           'nilai' => 0.00],
            ['kategori' => 'Dewasa',          'subkategori' => 'Dewasa Bekerja Tetapi Masih Ditanggung',         'nilai' => 0.00],
            ['kategori' => 'IPT',             'subkategori' => null,                                            'nilai' => 600.00],
            ['kategori' => 'Remaja',          'subkategori' => null,                                            'nilai' => 304.00],
            ['kategori' => 'Kanak-Kanak',     'subkategori' => 'Sekolah Rendah',                                'nilai' => 254.00],
            ['kategori' => 'Kanak-Kanak',     'subkategori' => 'Tadika',                                        'nilai' => 185.00],
            ['kategori' => 'Rumah Sewa',      'subkategori' => null,                                            'nilai' => 0.00],
            ['kategori' => 'Utiliti',         'subkategori' => null,                                            'nilai' => 0.00],
            ['kategori' => 'Pengangkutan',    'subkategori' => null,                                            'nilai' => 0.00],
            ['kategori' => 'Perubatan',       'subkategori' => null,                                            'nilai' => 0.00],
        ];

        foreach ($rows as $row) {
            DB::table('had_kifayah_settings')->updateOrInsert(
                ['kategori' => $row['kategori'], 'subkategori' => $row['subkategori']],
                array_merge($row, [
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
