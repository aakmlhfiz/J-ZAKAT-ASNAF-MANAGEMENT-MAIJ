<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseholdMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'household_id',
        'profil_asnaf_id',
        'nama',
        'ic',
        'hubungan',
        'umur',
        'pekerjaan',
        'pendidikan',
        'pendapatan_bulanan',
        'status_kesihatan',
        'applicant_status',
        'assistance_status',
        'profile_photo_path',
    ];

    protected $casts = [
        'umur' => 'integer',
        'pendapatan_bulanan' => 'decimal:2',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function profilAsnaf()
    {
        return $this->belongsTo(ProfilAsnaf::class);
    }

    public function assistanceHistories()
    {
        return $this->hasMany(AssistanceHistory::class);
    }

    public function getActiveAssistanceAttribute()
    {
        return $this->assistanceHistories()
            ->where('status', 'approved')
            ->get();
    }
}
