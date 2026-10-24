<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->string('letter_path')->nullable()->after('approval_remarks');
            $table->string('letter_type')->nullable()->after('letter_path');
            $table->timestamp('letter_generated_at')->nullable()->after('letter_type');
            $table->timestamp('letter_sent_at')->nullable()->after('letter_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropColumn(['letter_path', 'letter_type', 'letter_generated_at', 'letter_sent_at']);
        });
    }
};
