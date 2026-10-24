<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('had_kifayah_settings');
        Schema::create('had_kifayah_settings', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100);
            $table->string('subkategori', 100)->nullable();
            $table->decimal('nilai', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['kategori', 'subkategori'], 'uk_kategori_subkategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('had_kifayah_settings');
    }
};
