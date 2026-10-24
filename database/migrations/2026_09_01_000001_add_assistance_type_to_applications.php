<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'assistance_type')) {
                $table->string('assistance_type')->nullable()->after('income_source')->index();
            }
            if (! Schema::hasColumn('applications', 'assistance_frequency')) {
                $table->string('assistance_frequency', 20)->nullable()->after('assistance_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumnIfExists('assistance_frequency');
            $table->dropColumnIfExists('assistance_type');
        });
    }
};
