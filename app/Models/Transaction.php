<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'reception_user_id',
        'payment_method',
        'mpesa_reference',
        'is_free_haircut',
        'subtotal',
        'discount',
        'total',
        'notes',
        'served_at',
        'amount_tendered',
        'change_due',
        'credit_used',
        'credit_stored',
        'cash_paid',
        'mpesa_paid',
    ];

    protected function casts(): array
    {
        return [
            'is_free_haircut' => 'boolean',
            'subtotal'        => 'decimal:2',
            'discount'        => 'decimal:2',
            'total'           => 'decimal:2',
            'amount_tendered' => 'decimal:2',
            'change_due'      => 'decimal:2',
            'credit_used'     => 'decimal:2',
            'credit_stored'   => 'decimal:2',
            'cash_paid'       => 'decimal:2',
            'mpesa_paid'      => 'decimal:2',
            'served_at'       => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reception_user_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
