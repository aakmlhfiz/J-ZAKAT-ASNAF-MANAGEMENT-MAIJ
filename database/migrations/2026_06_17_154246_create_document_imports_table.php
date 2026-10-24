<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->string('file_hash', 64)->index();
            $table->string('status', 20)->default('processing')->index();
            $table->longText('ocr_text')->nullable();
            $table->string('ocr_engine', 50)->nullable();
            $table->timestamp('ocr_started_at')->nullable();
            $table->timestamp('ocr_completed_at')->nullable();
            $table->json('extracted_data')->nullable();
            $table->decimal('overall_confidence', 5, 2)->nullable();
            $table->json('matched_labels')->nullable();
            $table->string('profil_asnaf_ic', 20)->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_imports');
    }
};
