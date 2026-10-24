<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profil_asnafs', function (Blueprint $table) {

            $table->id();

            $table->string('nama');

            $table->string('ic')->unique();

            $table->string('ic_ketua_rumah')->nullable();

            $table->string('hubungan')->nullable();

            $table->string('telefon')->nullable();

            $table->text('alamat')->nullable();

            $table->decimal('pendapatan', 10, 2)->default(0);

            $table->integer('bilangan_tanggungan')->default(0);

            $table->decimal('sewa', 10, 2)->default(0);

            $table->decimal('utiliti', 10, 2)->default(0);

            $table->decimal('had_kifayah', 10, 2)->default(0);

            $table->string('status_kelayakan')->default('Dalam Semakan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_asnafs');
    }
};
