<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $fillable = [
        'nama_fail',
        'jumlah_rekod',
        'berjaya',
        'gagal',
    ];
}
