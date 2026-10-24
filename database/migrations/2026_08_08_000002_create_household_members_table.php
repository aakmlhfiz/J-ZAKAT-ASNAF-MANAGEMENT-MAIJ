<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('profil_asnaf_id')->nullable()->constrained('profil_asnafs')->nullOnDelete();
            $table->string('nama');
            $table->string('ic')->unique();
            $table->string('hubungan');
            $table->integer('umur')->default(0);
            $table->string('pekerjaan')->nullable();
            $table->string('pendidikan')->nullable();
            $table->decimal('pendapatan_bulanan', 10, 2)->default(0);
            $table->string('status_kesihatan')->nullable();
            $table->string('applicant_status')->default('active');
            $table->string('assistance_status')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};
