<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->json('ocr_result')->nullable()->after('borang_data');
        });
    }

    public function down(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->dropColumn('ocr_result');
        });
    }
};
