<?php

namespace App\Models;

use App\Services\HadAlKifayahService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfilAsnaf extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'ic',
        'ic_ketua_rumah',
        'hubungan',
        'status_perkahwinan',
        'merokok',

        'telefon',
        'no_telefon_bimbit',
        'no_telefon_rumah',
        'kesihatan',
        'alamat',
        'daerah',
        'bandar',
        'poskod',
        'dun',
        'parlimen',

        'pendapatan',
        'bilangan_tanggungan',

        'sewa',
        'utiliti',

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
        'sebab_memohon',
        'borang_data',

        'had_kifayah',
        'ai_score',
        'ai_recommendation',
        'status_kelayakan',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'documents_requested_list',
        'documents_request_note',
        'documents_requested_at',
        'documents_requested_by',
        'letter_path',
        'letter_type',
        'letter_generated_at',
        'letter_sent_at',
    ];

    public function statusSlug(): string
    {
        return strtolower(str_replace(' ', '-', $this->status_kelayakan ?? ''));
    }

    public function statusTone(): string
    {
        return match ($this->statusSlug()) {
            'layak' => 'success',
            'tidak-layak' => 'danger',
            default => 'warning',
        };
    }

    public function approvalStatusSlug(): string
    {
        return $this->approval_status ?? 'pending';
    }

    public function approvalStatusTone(): string
    {
        return match ($this->approval_status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    public function approvalStatusLabel(): string
    {
        return match ($this->approval_status) {
            'approved' => 'Diluluskan',
            'rejected' => 'Ditolak',
            default => 'Menunggu Kelulusan',
        };
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending' || $this->approval_status === null;
    }

    public function confidenceScore(): int
    {
        $score = (float) ($this->ai_score ?? 0);

        return match ($this->status_kelayakan) {
            HadAlKifayahService::STATUS_LAYAK => $score > 0 ? (int) $score : 90,
            HadAlKifayahService::STATUS_TIDAK_LAYAK => $score > 0 ? (int) (100 - $score) : 80,
            default => 55,
        };
    }

    public static function monthlyAggregate(int $months = 12): Collection
    {
        $driver = DB::connection()->getDriverName();

        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'mysql', 'mariadb' => "DATE_FORMAT(created_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        return static::selectRaw("{$monthExpression} as month, status_kelayakan, COUNT(*) as cnt")
            ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
            ->groupBy('month', 'status_kelayakan')
            ->get();
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function householdMember()
    {
        return $this->hasOne(HouseholdMember::class, 'profil_asnaf_id');
    }

    protected function casts(): array
    {
        return [
            'pendapatan' => 'decimal:2',
            'bilangan_tanggungan' => 'integer',
            'sewa' => 'decimal:2',
            'utiliti' => 'decimal:2',
            'had_kifayah' => 'decimal:2',
            'ai_score' => 'decimal:2',
            'pengangkutan_input' => 'decimal:2',
            'perubatan_input' => 'decimal:2',
            'n_isteri_suami' => 'integer',
            'n_ibu_bapa' => 'integer',
            'n_dewasa_tidak_bekerja' => 'integer',
            'n_dewasa_bekerja_tanggung' => 'integer',
            'n_ipt' => 'integer',
            'n_remaja' => 'integer',
            'n_kanak_rendah' => 'integer',
            'n_kanak_tadika' => 'integer',
            'merokok' => 'boolean',
            'approved_at' => 'datetime',
            'letter_generated_at' => 'datetime',
            'letter_sent_at' => 'datetime',
            'documents_requested_list' => 'array',
            'documents_requested_at' => 'datetime',
            'borang_data' => 'array',
        ];
    }
}
