<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'estimated_days_min',
        'estimated_days_max',
        'price_multiplier',
        'volumetric_divisor',
        'min_weight_kg',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_multiplier' => 'decimal:2',
        'min_weight_kg' => 'decimal:2',
    ];

    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function getEtdRangeAttribute(): string
    {
        if ($this->estimated_days_min === $this->estimated_days_max) {
            return $this->estimated_days_min === 0 ? 'Same Day' : "{$this->estimated_days_min} Day";
        }
        return "{$this->estimated_days_min}-{$this->estimated_days_max} Days";
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->code) {
            'EXP', 'YES', 'BEST' => 'bg-danger',
            'REG', 'EZ', 'SIUNT' => 'bg-primary',
            'CARGO', 'JTR', 'GOKIL' => 'bg-warning text-dark',
            'SAMEDAY' => 'bg-success',
            'ECO' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
