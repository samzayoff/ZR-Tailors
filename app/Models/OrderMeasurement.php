<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeasurement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_garment_id',
        'measurement_point_id',
        'value',
    ];

    public function orderGarment(): BelongsTo
    {
        return $this->belongsTo(OrderGarment::class);
    }

    public function measurementPoint(): BelongsTo
    {
        return $this->belongsTo(MeasurementPoint::class);
    }
}
