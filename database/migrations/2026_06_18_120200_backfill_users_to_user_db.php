<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $admin = DB::connection('admin');
        $user = DB::connection('user');

        $userRows = $admin->table('admins')->where('role', 'user')->get();

        if ($userRows->isEmpty()) {
            return;
        }

        $adminColumns = $admin->getSchemaBuilder()->getColumnListing('admins');
        $userColumns = $user->getSchemaBuilder()->getColumnListing('users');

        $skip = [
            'role',
            'department',
            'two_factor_secret',
            'two_factor_confirmed_at',
        ];

        $payload = [];
        foreach ($userRows as $row) {
            $row = (array) $row;
            $row = array_intersect_key($row, array_flip($userColumns));
            $row = array_diff_key($row, array_flip($skip));
            $payload[] = $row;
        }

        foreach (array_chunk($payload, 100) as $chunk) {
            $user->table('users')->insert($chunk);
        }

        $emails = $userRows->pluck('email')->filter()->all();
        if (! empty($emails)) {
            $tokens = $admin->table('password_reset_tokens')
                ->whereIn('email', $emails)
                ->get();

            $tokenColumns = $user->getSchemaBuilder()->getColumnListing('password_reset_tokens');

            foreach ($tokens as $t) {
                $t = array_intersect_key((array) $t, array_flip($tokenColumns));
                $user->table('password_reset_tokens')->insert($t);
            }
        }
    }

    public function down(): void
    {
        $admin = DB::connection('admin');
        $user = DB::connection('user');

        $emails = $admin->table('admins')->where('role', 'user')->pluck('email')->filter()->all();

        if (! empty($emails)) {
            $user->table('password_reset_tokens')->whereIn('email', $emails)->delete();
        }

        $user->table('users')->truncate();
    }
};
