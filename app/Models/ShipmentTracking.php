<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'branch_id',
        'user_id',
        'status',
        'title',
        'location_city',
        'location_details',
        'description',
        'manifest_code',
        'occurred_at',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return match ($this->status) {
            'booking_created' => 'fa-box',
            'picked_up' => 'fa-dolly',
            'received_at_origin_hub', 'received_at_transit_hub' => 'fa-warehouse',
            'manifested_in_transit' => 'fa-truck-fast',
            'received_at_dest_branch' => 'fa-building-circle-check',
            'out_for_delivery' => 'fa-motorcycle',
            'delivered' => 'fa-circle-check',
            'failed_attempt' => 'fa-triangle-exclamation',
            'returned_to_sender' => 'fa-arrow-rotate-left',
            default => 'fa-location-dot',
        };
    }

    public function getIconColorAttribute(): string
    {
        return match ($this->status) {
            'delivered' => 'text-success',
            'out_for_delivery' => 'text-warning',
            'manifested_in_transit', 'received_at_origin_hub', 'received_at_transit_hub' => 'text-primary',
            'failed_attempt' => 'text-danger',
            'returned_to_sender' => 'text-danger',
            default => 'text-secondary',
        };
    }
}
