<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'category', 'code', 'name_en', 'name_ur', 'icon', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * All options grouped by category, ordered for view rendering.
     */
    public static function grouped(): array
    {
        return self::orderBy('sort_order')
            ->get()
            ->groupBy('category')
            ->toArray();
    }
}
