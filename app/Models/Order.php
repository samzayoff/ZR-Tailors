<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'customer_id',
        'booking_date',
        'delivery_date',
        'quantity',
        'price',
        'advance_paid',
        'colour_note',
        'extra_notes',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'delivery_date' => 'date',
        'price' => 'decimal:2',
        'advance_paid' => 'decimal:2',
    ];

    // ── Relationships 

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function garments(): HasMany
    {
        return $this->hasMany(OrderGarment::class);
    }

    public function designOptions(): BelongsToMany
    {
        return $this->belongsToMany(
            DesignOption::class,
            'order_design_options',
            'order_id',
            'design_option_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Helpers 
    public function measurementsFor(string $garmentCode): array
    {
        $garment = $this->garments()
            ->whereHas('garmentType', fn($q) => $q->where('code', $garmentCode))
            ->with('measurements.measurementPoint')
            ->first();

        if (!$garment) {
            return [];
        }

        $map = [];
        foreach ($garment->measurements as $m) {
            $map[$m->measurementPoint->code] = $m->value;
        }
        return $map;
    }

    /**
     * Returns a Set of selected design_option IDs for this order.
     */
    public function selectedOptionIds(): array
    {
        return $this->designOptions()->pluck('design_options.id')->toArray();
    }

    /**
     * Generate next order number (max + 1).
     */
    public static function nextOrderNo(): string
    {
        $max = self::whereRaw("order_no REGEXP '^[0-9]+$'")->pluck('order_no')
            ->map(fn($v) => (int) $v)->max();
        return $max ? (string) ($max + 1) : '1';
    }
}