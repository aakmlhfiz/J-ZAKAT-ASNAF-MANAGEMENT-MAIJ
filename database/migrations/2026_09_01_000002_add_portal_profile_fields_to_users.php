<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mukim')) {
                $table->string('mukim', 100)->nullable()->after('district');
            }
            if (! Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation')->nullable()->after('mukim');
            }
            if (! Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 20)->nullable()->after('occupation');
            }
            if (! Schema::hasColumn('users', 'monthly_income')) {
                $table->decimal('monthly_income', 10, 2)->default(0)->after('marital_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('monthly_income');
            $table->dropColumnIfExists('marital_status');
            $table->dropColumnIfExists('occupation');
            $table->dropColumnIfExists('mukim');
        });
    }
};
