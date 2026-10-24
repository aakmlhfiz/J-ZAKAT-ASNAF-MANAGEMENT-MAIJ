<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ic_no')) {
                $table->string('ic_no')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ic_no')) {
                $table->string('ic_no')->nullable(false)->change();
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable(false)->change();
            }
        });
    }
};
