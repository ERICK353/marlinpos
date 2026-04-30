<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLog extends Model
{
    protected $fillable = [
        'user_id',
        'log_date',
        'logged_in_at',
        'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'log_date'      => 'date',
            'logged_in_at'  => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns a human-readable duration string or null if still active.
     */
    public function durationLabel(): ?string
    {
        if (! $this->logged_out_at) {
            return null;
        }

        $minutes = (int) $this->logged_in_at->diffInMinutes($this->logged_out_at);
        $h       = intdiv($minutes, 60);
        $m       = $minutes % 60;

        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }
}
