<?php

namespace Database\Seeders;

use App\Models\AdminInvitation;
use Illuminate\Database\Seeder;

class AdminInvitationSeeder extends Seeder
{
    public function run(): void
    {
        if (AdminInvitation::count() > 0) {
            return;
        }

        AdminInvitation::create([
            'code' => 'MAIJ-DEMO-0000-0001',
            'notes' => 'Kod undangan pertama — janakan melalui php artisan db:seed. Sah 90 hari.',
            'expires_at' => now()->addDays(90),
            'is_active' => true,
        ]);

        $this->command->info('Kod undangan admin pertama telah dijana: MAIJ-DEMO-0000-0001');
        $this->command->info('Sah sehingga: '.now()->addDays(90)->format('d M Y'));
        $this->command->info('Gunakan kod ini untuk mendaftar sebagai admin di /register');
    }
}
