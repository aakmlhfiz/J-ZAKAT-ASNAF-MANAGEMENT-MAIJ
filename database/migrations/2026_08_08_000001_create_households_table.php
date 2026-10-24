<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('household_id')->unique();
            $table->text('alamat')->nullable();
            $table->string('daerah')->nullable();
            $table->string('mukim')->nullable();
            $table->string('parlimen')->nullable();
            $table->string('dun')->nullable();
            $table->decimal('total_income', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->decimal('ai_score', 5, 2)->default(0);
            $table->string('eligibility_status')->default('Dalam Semakan');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
