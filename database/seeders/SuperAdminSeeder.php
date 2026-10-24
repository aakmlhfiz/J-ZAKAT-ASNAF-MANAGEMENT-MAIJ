<?php

namespace Database\Seeders;

use App\Mail\SetupPasswordMail;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('MAIJ_SUPER_ADMIN_EMAIL');
        $name = env('MAIJ_SUPER_ADMIN_NAME', 'Super Admin');

        if (! $email) {
            $this->command->error('MAIJ_SUPER_ADMIN_EMAIL tidak ditetapkan dalam .env');

            return;
        }

        $defaultPassword = env('MAIJ_SUPER_ADMIN_PASSWORD', 'j-zakat2024');

        $admin = Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
                'must_change_password' => false,
                'status' => 'active',
                'dark_mode' => 'system',
                'language' => 'ms',
                'notification_email' => true,
                'notification_push' => true,
                'notification_sms' => false,
            ]
        );

        $this->command->info("Akaun Super Admin sedia: {$admin->email}");
        $this->command->newLine();
        $this->command->line("  Email    : {$admin->email}");
        $this->command->line("  Password : {$defaultPassword}");
        $this->command->newLine();
        $this->command->warn('  Tukar kata laluan ini selepas log masuk kali pertama.');

        if ($admin->wasRecentlyCreated || $admin->wasChanged()) {
            try {
                $status = Password::sendResetLink(
                    ['email' => $email],
                    function ($user, $token) {
                        Mail::to($user->email)->send(new SetupPasswordMail($user, $token));
                    }
                );

                $this->command->info('  Pautan tetapan kata laluan telah dihantar.');
            } catch (\Throwable $e) {
                Log::error('Gagal menjana pautan tetapan kata laluan.', [
                    'error' => $e->getMessage(),
                ]);
                $this->command->warn('  Emel tetapan kata laluan gagal dihantar (MAIL_MAILER=log).');
            }
        }
    }
}
