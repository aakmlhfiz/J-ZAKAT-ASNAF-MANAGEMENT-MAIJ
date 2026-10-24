<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('application_no')->unique();

            // Step 1: Personal
            $table->string('full_name');
            $table->string('ic_no', 12);
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('phone');
            $table->string('email');
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('postcode', 5)->nullable();

            // Step 2: Family
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->integer('household_count')->default(1);
            $table->json('dependents')->nullable();

            // Step 3: Financial
            $table->string('occupation')->nullable();
            $table->decimal('monthly_income', 10, 2)->default(0);
            $table->decimal('spouse_income', 10, 2)->default(0);
            $table->decimal('total_household_income', 10, 2)->default(0);
            $table->enum('income_source', ['employment', 'business', 'pension', 'none', 'other'])->nullable();

            // Step 4: Documents
            $table->string('ic_document')->nullable();
            $table->string('salary_document')->nullable();
            $table->json('supporting_documents')->nullable();

            // Status
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'incomplete'])->default('draft');
            $table->text('admin_remarks')->nullable();
            $table->text('user_remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // AI Results
            $table->decimal('had_kifayah_result', 10, 2)->nullable();
            $table->integer('ai_score')->nullable();
            $table->enum('ai_recommendation', ['approve', 'reject', 'review'])->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('application_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
