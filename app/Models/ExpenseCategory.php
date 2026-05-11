<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Only active, non-deleted categories for dropdowns */
    public static function options(): array
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }
}

