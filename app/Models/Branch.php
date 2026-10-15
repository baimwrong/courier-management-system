<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'city',
        'province',
        'postal_code',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function couriers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'courier');
    }

    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_branch_id');
    }

    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_branch_id');
    }

    public function currentShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'current_branch_id');
    }

    public function manifests(): HasMany
    {
        return $this->hasMany(Manifest::class, 'origin_branch_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'central_hub' => 'Central Hub',
            'sorting_hub' => 'Sorting Hub',
            'branch' => 'Branch Office',
            'drop_point' => 'Drop Point',
            'agent_counter' => 'Agent Counter',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
