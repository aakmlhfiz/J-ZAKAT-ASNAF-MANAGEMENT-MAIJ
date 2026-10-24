<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_score')->default(0)->after('status_kelayakan');
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropColumn('ai_score');
        });
    }
};
