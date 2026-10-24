<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->unsignedTinyInteger('n_isteri_suami')->default(0)->after('utiliti');
            $table->unsignedTinyInteger('n_ibu_bapa')->default(0)->after('n_isteri_suami');
            $table->unsignedTinyInteger('n_dewasa_tidak_bekerja')->default(0)->after('n_ibu_bapa');
            $table->unsignedTinyInteger('n_dewasa_bekerja_tanggung')->default(0)->after('n_dewasa_tidak_bekerja');
            $table->unsignedTinyInteger('n_ipt')->default(0)->after('n_dewasa_bekerja_tanggung');
            $table->unsignedTinyInteger('n_remaja')->default(0)->after('n_ipt');
            $table->unsignedTinyInteger('n_kanak_rendah')->default(0)->after('n_remaja');
            $table->unsignedTinyInteger('n_kanak_tadika')->default(0)->after('n_kanak_rendah');
            $table->enum('perumahan', ['sendiri', 'sewa'])->default('sendiri')->after('n_kanak_tadika');
            $table->decimal('pengangkutan_input', 10, 2)->default(0)->after('perumahan');
            $table->decimal('perubatan_input', 10, 2)->default(0)->after('pengangkutan_input');
        });
    }

    public function down(): void
    {
        Schema::table('profil_asnafs', function (Blueprint $table) {
            $table->dropColumn([
                'n_isteri_suami',
                'n_ibu_bapa',
                'n_dewasa_tidak_bekerja',
                'n_dewasa_bekerja_tanggung',
                'n_ipt',
                'n_remaja',
                'n_kanak_rendah',
                'n_kanak_tadika',
                'perumahan',
                'pengangkutan_input',
                'perubatan_input',
            ]);
        });
    }
};
