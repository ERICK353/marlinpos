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
        'free_shaves_used',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    // ── Loyalty helpers ───────────────────────────────────────────────────────

    public function isEligibleForFreeShave(): bool
    {
        return $this->loyalty_count >= 9;
    }

    public function loyaltyProgressLabel(): string
    {
        return $this->loyalty_count . ' / 9';
    }

    public function loyaltyProgressPercent(): int
    {
        return (int) min(100, round(($this->loyalty_count / 9) * 100));
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
