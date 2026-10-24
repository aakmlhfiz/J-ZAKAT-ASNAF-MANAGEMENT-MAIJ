<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->json('borang_data')->nullable()->after('sebab_memohon');
            $table->text('ai_recommendation')->nullable()->after('ai_score');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('borang_scan_id')->nullable()->after('supporting_documents')
                ->constrained('document_imports')->nullOnDelete();
            $table->json('borang_data')->nullable()->after('borang_scan_id');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['borang_scan_id']);
            $table->dropColumn(['borang_scan_id', 'borang_data']);
        });

        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropColumn(['borang_data', 'ai_recommendation']);
        });
    }
};
