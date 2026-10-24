<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'role', 'type', 'title', 'body',
        'icon', 'color', 'link', 'data', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, $user): void
    {
        $role = $user instanceof PortalUser ? 'user' : 'admin';

        $query->where(function ($q) use ($user, $role) {
            $q->where('user_id', $user->id)
                ->orWhere('role', $role);
        });
    }

    public function scopeForRole(Builder $query, string $role): void
    {
        $query->where('role', $role);
    }

    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }
}
