<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_role',
        'user_email',
        'action',
        'category',
        'description',
        'ip_address',
        'user_agent',
        'browser',
        'device',
        'os',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForRole(Builder $query, ?string $role): Builder
    {
        return $query->where('user_role', $role);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeAction(Builder $query, ?string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    public function scopeRecent(Builder $query, int $limit = 20): Builder
    {
        return $query->latest('created_at')->limit($limit);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'auth' => 'Pengesahan',
            'application' => 'Permohonan',
            'data' => 'Data',
            'security' => 'Keselamatan',
            'system' => 'Sistem',
            'money' => 'Kewangan',
            'audit' => 'Audit Semakan',
            default => ucfirst((string) $this->category),
        };
    }

    public function actionLabel(): string
    {
        return str_replace('.', ' › ', (string) $this->action);
    }
}
