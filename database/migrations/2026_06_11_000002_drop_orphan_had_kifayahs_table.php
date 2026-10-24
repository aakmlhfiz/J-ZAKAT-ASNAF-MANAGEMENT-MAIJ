<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('had_kifayahs');
    }

    public function down(): void
    {
        // Cannot recreate the original schema; left empty intentionally.
    }
};
