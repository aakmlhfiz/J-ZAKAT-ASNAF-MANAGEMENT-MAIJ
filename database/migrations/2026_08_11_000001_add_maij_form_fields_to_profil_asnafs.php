<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->string('daerah')->nullable()->after('alamat');
            $table->string('bandar')->nullable()->after('daerah');
            $table->string('poskod', 5)->nullable()->after('bandar');
            $table->string('dun')->nullable()->after('poskod');
            $table->string('parlimen')->nullable()->after('dun');
            $table->string('no_telefon_bimbit')->nullable()->after('telefon');
            $table->string('no_telefon_rumah')->nullable()->after('no_telefon_bimbit');
            $table->string('kesihatan')->nullable()->after('no_telefon_rumah');
            $table->string('status_perkahwinan')->nullable()->after('hubungan');
            $table->boolean('merokok')->default(false)->after('status_perkahwinan');
            $table->text('sebab_memohon')->nullable()->after('perubatan_input');
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropColumn([
                'daerah',
                'bandar',
                'poskod',
                'dun',
                'parlimen',
                'no_telefon_bimbit',
                'no_telefon_rumah',
                'kesihatan',
                'status_perkahwinan',
                'merokok',
                'sebab_memohon',
            ]);
        });
    }
};
