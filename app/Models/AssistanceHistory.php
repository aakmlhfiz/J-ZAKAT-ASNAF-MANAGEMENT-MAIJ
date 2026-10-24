<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssistanceHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'household_id',
        'household_member_id',
        'jenis_bantuan',
        'amount',
        'frequency',
        'status',
        'approved_date',
        'expiry_date',
        'approval_officer',
        'reference_number',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function member()
    {
        return $this->belongsTo(HouseholdMember::class, 'household_member_id');
    }

    public static function jenisBantuanOptions(): array
    {
        return [
            'Sara Hidup' => 'Sara Hidup',
            'Perubatan' => 'Perubatan',
            'Dialisis' => 'Dialisis',
            'Sewa Rumah' => 'Sewa Rumah',
            'IPT' => 'IPT',
            'Sekolah' => 'Sekolah',
            'Berdikari' => 'Berdikari',
            'Peralatan' => 'Peralatan',
            'Emergency' => 'Emergency',
        ];
    }
}
