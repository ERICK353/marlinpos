<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'loyalty_count',
        'total_visits',
        'free_haircuts_used',
        'enrolled_at',
        'credit_balance',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'    => 'datetime',
            'credit_balance' => 'decimal:2',
        ];
    }

    // ── Wallet Helpers ─────────────────────────────────────────────────────────

    public function addCredit(float $amount): void
    {
        $this->increment('credit_balance', $amount);
    }

    public function deductCredit(float $amount): void
    {
        $this->decrement('credit_balance', min($amount, (float) $this->credit_balance));
    }

    public function hasCredit(): bool
    {
        return (float) $this->credit_balance > 0;
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
