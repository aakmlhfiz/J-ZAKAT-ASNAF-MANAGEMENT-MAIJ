<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->json('documents_requested_list')->nullable()->after('approval_remarks');
            $table->text('documents_request_note')->nullable()->after('documents_requested_list');
            $table->timestamp('documents_requested_at')->nullable()->after('documents_request_note');
            $table->foreignId('documents_requested_by')->nullable()->after('documents_requested_at')
                ->constrained('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropForeign(['documents_requested_by']);
            $table->dropColumn([
                'documents_requested_list',
                'documents_request_note',
                'documents_requested_at',
                'documents_requested_by',
            ]);
        });
    }
};
