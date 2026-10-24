<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('household_member_id')->constrained('household_members')->cascadeOnDelete();
            $table->string('jenis_bantuan');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('frequency')->default('one_off');
            $table->string('status')->default('approved');
            $table->date('approved_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('approval_officer')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance_histories');
    }
};
