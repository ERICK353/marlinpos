<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'commission_rate',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'commission_rate'   => 'decimal:2',
        ];
    }

    // ── Role helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isReception(): bool
    {
        return $this->role === 'reception';
    }

    // ── Filament panel access ─────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'     => true, // Central landlord users have no role, so we allow access if they are authenticated
            'shop'      => $this->isAdmin(), // The old 'admin' panel is now 'shop' for tenants
            'staff'     => $this->isStaff(),
            'reception' => $this->isReception(),
            default     => false,
        };
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'staff_user_id');
    }

    public function receptionTransactions()
    {
        return $this->hasMany(Transaction::class, 'reception_user_id');
    }

    public function staffLogs()
    {
        return $this->hasMany(StaffLog::class);
    }
}
