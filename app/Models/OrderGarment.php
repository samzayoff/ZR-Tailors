<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderGarment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'garment_type_id',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function garmentType(): BelongsTo
    {
        return $this->belongsTo(GarmentType::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(OrderMeasurement::class);
    }
}
