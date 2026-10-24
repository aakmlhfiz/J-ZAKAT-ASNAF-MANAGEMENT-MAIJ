<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('department');
            }
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            }
            if (! Schema::hasColumn('users', 'dark_mode')) {
                $table->string('dark_mode')->default('system')->after('two_factor_confirmed_at');
            }
            if (! Schema::hasColumn('users', 'language')) {
                $table->string('language')->default('ms')->after('dark_mode');
            }
            if (! Schema::hasColumn('users', 'notification_email')) {
                $table->boolean('notification_email')->default(true)->after('language');
            }
            if (! Schema::hasColumn('users', 'notification_push')) {
                $table->boolean('notification_push')->default(true)->after('notification_email');
            }
            if (! Schema::hasColumn('users', 'notification_sms')) {
                $table->boolean('notification_sms')->default(false)->after('notification_push');
            }
        });

        DB::statement(
            'UPDATE users SET department = COALESCE(organization, position) WHERE department IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'department', 'status', 'two_factor_secret',
                'two_factor_confirmed_at', 'dark_mode', 'language',
                'notification_email', 'notification_push', 'notification_sms',
            ]);
        });
    }
};
