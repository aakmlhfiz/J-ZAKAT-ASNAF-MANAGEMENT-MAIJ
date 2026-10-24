<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PortalUser extends Authenticatable implements CanResetPasswordContract
{
    use CanResetPassword, HasFactory, \Illuminate\Database\Eloquent\SoftDeletes, Notifiable;

    protected $connection = 'user';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'firebase_uid',
        'ic_no',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'address',
        'district',
        'mukim',
        'occupation',
        'marital_status',
        'monthly_income',
        'state',
        'postcode',
        'password',
        'profile_photo',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'status',
        'dark_mode',
        'language',
        'notification_email',
        'notification_push',
        'notification_sms',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'notification_email' => 'boolean',
            'notification_push' => 'boolean',
            'notification_sms' => 'boolean',
            'monthly_income' => 'decimal:2',
        ];
    }

    public function roleLabel(): string
    {
        return 'Pengguna Portal';
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function profileCompletion(): int
    {
        $fields = ['name', 'ic_no', 'phone', 'address', 'district', 'state', 'postcode', 'date_of_birth', 'gender', 'profile_photo'];
        $filled = 0;
        foreach ($fields as $field) {
            if (! empty($this->$field)) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    public function securityScore(): int
    {
        $score = 0;
        if ($this->email_verified_at) {
            $score += 25;
        }
        if (! empty($this->phone)) {
            $score += 25;
        }
        if (! empty($this->profile_photo)) {
            $score += 25;
        }
        if ($this->hasTwoFactorEnabled()) {
            $score += 25;
        }

        return min($score, 100);
    }

    public function applications(): HasMany
    {
        $related = (new Application)->setConnection('admin');

        return $this->newHasMany($related->newQuery(), $this, 'user_id', 'id');
    }
}
