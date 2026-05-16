<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'service_id',
        'staff_user_id',
        'quantity',
        'unit_price',
        'line_total',
        'discount_amount',
        'commission_rate',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'        => 'decimal:2',
            'line_total'        => 'decimal:2',
            'discount_amount'   => 'decimal:2',
            'commission_rate'   => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id')->withTrashed();
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            // Auto-populate price if missing (null, empty string, or 0)
            if ($item->service_id && (empty($item->unit_price) || empty($item->line_total))) {
                $service = Service::find($item->service_id);
                if ($service) {
                    // Only fill if not already set (to avoid overwriting custom prices like the 250 for free shaves)
                    if (empty($item->unit_price)) $item->unit_price = $service->price;
                    if (empty($item->line_total)) $item->line_total = $service->price * ($item->quantity ?? 1);
                }
            }

            // Auto-populate commission rate if missing
            if ($item->staff_user_id && !$item->commission_rate) {
                $staff = User::find($item->staff_user_id);
                if ($staff) {
                    $item->commission_rate = $staff->commission_rate ?? 40;
                }
            }

            // Calculate commission amount
            if ($item->unit_price && $item->commission_rate && !$item->commission_amount) {
                $item->commission_amount = ($item->unit_price * $item->commission_rate) / 100;
            }
        });
    }
}
