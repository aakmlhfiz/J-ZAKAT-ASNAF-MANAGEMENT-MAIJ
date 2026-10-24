<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'household_id',
        'alamat',
        'daerah',
        'mukim',
        'parlimen',
        'dun',
        'total_income',
        'total_expenses',
        'ai_score',
        'eligibility_status',
        'status',
    ];

    protected $casts = [
        'total_income' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'ai_score' => 'decimal:2',
    ];

    public function members()
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function assistanceHistories()
    {
        return $this->hasMany(AssistanceHistory::class);
    }

    public function ketuaIsiRumah()
    {
        return $this->hasMany(HouseholdMember::class)->where('hubungan', 'Ketua Isi Rumah');
    }

    public function getKetuaAttribute()
    {
        return $this->members()->where('hubungan', 'Ketua Isi Rumah')->first();
    }

    public function getTotalMembersAttribute(): int
    {
        return $this->members()->count();
    }

    public function getTotalMonthlyAssistanceAttribute(): float
    {
        return (float) $this->assistanceHistories()
            ->where('frequency', 'monthly')
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function getTotalOneOffAssistanceAttribute(): float
    {
        return (float) $this->assistanceHistories()
            ->where('frequency', 'one_off')
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function getTotalAssistanceReceivedAttribute(): float
    {
        return $this->total_monthly_assistance + $this->total_one_off_assistance;
    }

    public function getActiveAssistanceCountAttribute(): int
    {
        return $this->assistanceHistories()
            ->where('status', 'approved')
            ->count();
    }

    protected static function booted(): void
    {
        static::creating(function (Household $h) {
            if (empty($h->household_id)) {
                $count = static::count() + 1;
                $h->household_id = 'HR-'.date('Y').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
