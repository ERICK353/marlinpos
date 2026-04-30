<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'reception_user_id',
        'payment_method',
        'mpesa_reference',
        'is_free_shave',
        'subtotal',
        'discount',
        'total',
        'notes',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'is_free_shave' => 'boolean',
            'subtotal'      => 'decimal:2',
            'discount'      => 'decimal:2',
            'total'         => 'decimal:2',
            'served_at'     => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reception_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
