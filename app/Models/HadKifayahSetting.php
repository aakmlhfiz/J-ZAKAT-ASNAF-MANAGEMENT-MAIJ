<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HadKifayahSetting extends Model
{
    protected $table = 'had_kifayah_settings';

    protected $fillable = [
        'kategori',
        'subkategori',
        'nilai',
        'status',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
