<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('portal_user_id')
                ->constrained('applications')->nullOnDelete();
            $table->foreignId('replaces_document_id')->nullable()->after('application_id')
                ->constrained('document_imports')->nullOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->after('replaces_document_id')
                ->constrained('document_imports')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('ocr_completed_at');
            $table->foreignId('verified_by_admin_id')->nullable()->after('verified_at')
                ->constrained('admins')->nullOnDelete();
            $table->unsignedBigInteger('verified_by_portal_user_id')->nullable()->after('verified_by_admin_id')->index();
            $table->timestamp('reviewed_at')->nullable()->after('verified_by_portal_user_id');
            $table->foreignId('reviewed_by_admin_id')->nullable()->after('reviewed_at')
                ->constrained('admins')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('reviewed_by_admin_id');
            $table->json('verified_data')->nullable()->after('rejection_reason');
            $table->json('metadata')->nullable()->after('verified_data');

            $table->index(['document_type', 'status'], 'document_imports_type_status_index');
        });

        Schema::create('document_verified_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_import_id')->constrained('document_imports')->cascadeOnDelete();
            $table->string('field_key', 80);
            $table->string('field_group', 80)->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_numeric', 14, 2)->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('confidence_source', 40)->nullable();
            $table->unsignedInteger('source_page')->nullable();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->unsignedBigInteger('verified_by_portal_user_id')->nullable()->index();
            $table->string('applied_to_type', 40)->nullable();
            $table->unsignedBigInteger('applied_to_id')->nullable();
            $table->timestamps();

            $table->unique(['document_import_id', 'field_key'], 'document_verified_values_document_field_unique');
            $table->index(['applied_to_type', 'applied_to_id', 'field_key'], 'document_verified_values_applied_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verified_values');

        Schema::table('document_imports', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropForeign(['replaces_document_id']);
            $table->dropForeign(['duplicate_of_id']);
            $table->dropForeign(['verified_by_admin_id']);
            $table->dropForeign(['reviewed_by_admin_id']);
            $table->dropIndex('document_imports_type_status_index');
            $table->dropColumn([
                'application_id',
                'replaces_document_id',
                'duplicate_of_id',
                'verified_at',
                'verified_by_admin_id',
                'verified_by_portal_user_id',
                'reviewed_at',
                'reviewed_by_admin_id',
                'rejection_reason',
                'verified_data',
                'metadata',
            ]);
        });
    }
};
