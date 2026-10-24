<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->string('document_type', 50)->nullable()->default('generic')->after('file_hash')
                ->comment('generic|borang_bantuan_maij');
            $table->string('template', 100)->nullable()->after('document_type');
        });
    }

    public function down(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'template']);
        });
    }
};
