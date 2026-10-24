<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            HadKifayahSettingSeeder::class,
            ProfilAsnafDataSeeder::class,
            SampleFamiliesSeeder::class,
        ]);

        $this->command?->info('Running household:backfill...');
        Artisan::call('household:backfill');
        $this->command?->info(trim(Artisan::output()));
    }
}
