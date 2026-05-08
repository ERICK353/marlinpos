<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'loyalty_count',
        'total_visits',
        'free_haircuts_used',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    // ── Haircut Loyalty Program ────────────────────────────────────────────────
    public function isEligibleForFreeHaircut(): bool
    {
        return $this->loyalty_count >= 9;
    }

    public function haircutProgressLabel(): string
    {
        return $this->loyalty_count . ' / 9 Haircuts';
    }

    public function haircutProgressPercent(): int
    {
        return (int) min(100, round(($this->loyalty_count / 9) * 100));
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
