<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'created_by', 'used_by', 'notes',
        'expires_at', 'is_active', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'used_by');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
            ->whereNull('used_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && ! $this->used_at
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function statusBadge(): string
    {
        if ($this->used_at) {
            return 'Digunakan';
        }
        if (! $this->is_active) {
            return 'Dinyahaktifkan';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Luput';
        }

        return 'Aktif';
    }

    public function statusColor(): string
    {
        return match ($this->statusBadge()) {
            'Aktif' => 'success',
            'Digunakan' => 'info',
            'Luput' => 'warning',
            'Dinyahaktifkan' => 'danger',
            default => 'primary',
        };
    }

    public static function generateCode(): string
    {
        $segments = [];
        for ($s = 0; $s < 4; $s++) {
            $seg = '';
            for ($c = 0; $c < 4; $c++) {
                $seg .= strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 1));
            }
            $segments[] = $seg;
        }

        $code = 'MAIJ-'.implode('-', $segments);

        if (static::where('code', $code)->exists()) {
            return static::generateCode();
        }

        return $code;
    }
}
