<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GarmentType extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name_en', 'name_ur', 'icon', 'sort_order'];

    public function measurementPoints(): HasMany
    {
        return $this->hasMany(MeasurementPoint::class)->orderBy('sort_order');
    }
}
