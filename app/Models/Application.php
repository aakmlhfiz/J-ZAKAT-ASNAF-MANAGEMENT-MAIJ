<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'application_no', 'full_name', 'ic_no', 'date_of_birth',
        'gender', 'phone', 'email', 'address', 'district', 'state', 'postcode',
        'marital_status', 'household_count', 'dependents',
        'occupation', 'monthly_income', 'spouse_income', 'total_household_income',
        'income_source',         'ic_document', 'salary_document', 'supporting_documents',
        'assistance_type', 'assistance_frequency',
        'borang_scan_id', 'borang_data',
        'status', 'admin_remarks', 'user_remarks',
        'submitted_at', 'approved_at', 'rejected_at',
        'salary_reminder_sent_at',
        'reviewed_at', 'reviewed_by',
        'had_kifayah_result', 'ai_score', 'ai_recommendation',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'salary_reminder_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'dependents' => 'array',
        'supporting_documents' => 'array',
        'borang_data' => 'array',
        'monthly_income' => 'decimal:2',
        'spouse_income' => 'decimal:2',
        'total_household_income' => 'decimal:2',
        'had_kifayah_result' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function borangScan()
    {
        return $this->belongsTo(DocumentImport::class, 'borang_scan_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Application $app) {
            if (empty($app->application_no)) {
                $count = static::count() + 1;
                $app->application_no = 'APP-'.date('Y').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
