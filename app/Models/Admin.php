<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes, Notifiable;

    protected $connection = 'admin';

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'firebase_uid',
        'profile_photo',
        'department',
        'status',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'dark_mode',
        'language',
        'notification_email',
        'notification_push',
        'notification_sms',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'notification_email' => 'boolean',
            'notification_push' => 'boolean',
            'notification_sms' => 'boolean',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function securityScore(): int
    {
        $score = 0;
        if ($this->email_verified_at) {
            $score += 25;
        }
        if ($this->profile_photo) {
            $score += 25;
        }
        if ($this->hasTwoFactorEnabled()) {
            $score += 25;
        }
        $score += 25;

        return min($score, 100);
    }

    public function roleLabel(): string
    {
        return 'Pentadbir Sistem';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Digantung',
            default => ucfirst($this->status ?? 'Aktif'),
        };
    }

    public function profileCompletion(): int
    {
        $fields = ['name', 'email', 'profile_photo', 'department'];
        $filled = 0;
        foreach ($fields as $field) {
            if (! empty($this->$field)) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class);
    }
}
