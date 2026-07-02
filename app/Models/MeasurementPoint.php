<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeasurementPoint extends Model
{
    public $timestamps = false;

    protected $fillable = ['garment_type_id', 'code', 'name_en', 'name_ur', 'icon', 'sort_order'];

    public function garmentType(): BelongsTo
    {
        return $this->belongsTo(GarmentType::class);
    }
}
