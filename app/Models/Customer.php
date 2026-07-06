<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'reference',
        'address',
        'notes',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Find by phone or return null. Ignores spaces and dashes so
     * "0300-1234567" and "0300 1234567" are treated as the same number.
     */
    public static function findByPhone(string $phone): ?self
    {
        $normalized = preg_replace('/[\s\-]+/', '', $phone);

        if ($normalized === '') {
            return null;
        }

        return self::whereRaw(
            "REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?",
            [$normalized]
        )->first();
    }
}