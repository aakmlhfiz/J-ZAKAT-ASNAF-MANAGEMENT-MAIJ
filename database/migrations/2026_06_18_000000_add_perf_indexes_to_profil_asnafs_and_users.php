<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->index('status_kelayakan');
            $table->index('created_at');
            $table->index(['status_kelayakan', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('firebase_uid');
            $table->index('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropIndex(['status_kelayakan']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status_kelayakan', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['firebase_uid']);
            $table->dropIndex(['email_verified_at']);
        });
    }
};
