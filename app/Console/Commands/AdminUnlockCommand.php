<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminUnlockCommand extends Command
{
    protected $signature = 'admin:unlock
                            {email? : Email of the admin to unlock}
                            {--password= : New password (default: password123)}
                            {--check-only : Only check the admin status, do not modify}';

    protected $description = 'Unlock a stuck admin account by resetting must_change_password and optionally updating the password';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! $email) {
            $email = $this->ask('Admin email address');
        }

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("Admin with email '{$email}' not found in the admins table.");
            $this->line('Check that the database connection is correct and the admin was seeded.');
            $this->line('Run: php artisan db:seed --class=SuperAdminSeeder');

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $admin->name],
                ['Email', $admin->email],
                ['Status', $admin->status ?? 'N/A'],
                ['must_change_password', $admin->must_change_password ? 'TRUE (LOCKED)' : 'false (OK)'],
                ['Email Verified', $admin->email_verified_at
                    ? Carbon::parse((string) $admin->email_verified_at)?->format('Y-m-d H:i')
                    : 'No'],
            ]
        );

        if ($this->option('check-only')) {
            if ($admin->must_change_password) {
                $this->warn('Admin is LOCKED. Run this command without --check-only to unlock.');
            } else {
                $this->info('Admin is not locked and should be able to log in normally.');
            }

            if ($admin->must_change_password) {
                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        $password = $this->option('password') ?: 'password123';

        $admin->forceFill([
            'must_change_password' => false,
            'password' => $password,
            'status' => 'active',
        ])->save();

        $admin->refresh();

        if (! Hash::check($password, $admin->password)) {
            $this->error('CRITICAL: Password was not stored correctly! The cast may be double-hashing.');
            $this->line('Current hash: '.$admin->password);

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Admin account has been unlocked successfully!');
        $this->newLine();
        $this->line('You can now log in with:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $admin->email],
                ['Password', $password],
            ]
        );
        $this->newLine();
        $this->warn('IMPORTANT: Change this password immediately after logging in.');
        $this->line('Go to: Profile → Change Password');

        return self::SUCCESS;
    }
}
