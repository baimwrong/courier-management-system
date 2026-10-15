<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_city',
        'origin_province',
        'destination_city',
        'destination_province',
        'service_id',
        'base_weight_kg',
        'base_rate',
        'per_kg_rate',
        'estimated_etd',
        'is_active',
    ];

    protected $casts = [
        'base_weight_kg' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Calculate cost based on chargeable weight.
     */
    public function calculateCost(float $chargeableWeight): float
    {
        $weight = max($chargeableWeight, (float) $this->base_weight_kg);
        
        if ($weight <= (float) $this->base_weight_kg) {
            return (float) $this->base_rate;
        }

        $extraWeight = ceil($weight - (float) $this->base_weight_kg);
        return (float) $this->base_rate + ($extraWeight * (float) $this->per_kg_rate);
    }
}
