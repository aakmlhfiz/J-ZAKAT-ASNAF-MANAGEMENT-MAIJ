<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->integer('page_count')->default(1)->after('file_size');
            $table->json('validation_results')->nullable()->after('extracted_data');
            $table->json('household_members_extracted')->nullable()->after('validation_results');
            $table->json('duplicate_warnings')->nullable()->after('household_members_extracted');
            $table->json('assistance_histories')->nullable()->after('duplicate_warnings');
            $table->foreignId('household_id')->nullable()->after('profil_asnaf_ic')->constrained('households')->nullOnDelete();
        });

        DB::statement("ALTER TABLE document_imports MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'processing' COMMENT 'processing|extracted|needs_review|committed|failed'");
    }

    public function down(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropColumn([
                'page_count',
                'validation_results',
                'household_members_extracted',
                'duplicate_warnings',
                'assistance_histories',
                'household_id',
            ]);
        });

        DB::statement("ALTER TABLE document_imports MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'processing'");
    }
};
