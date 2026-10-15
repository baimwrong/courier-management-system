<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'booking_date',
        'origin_branch_id',
        'destination_branch_id',
        'service_id',
        'creator_id',
        'current_branch_id',
        'assigned_courier_id',
        'customer_id',

        // Sender
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_city',
        'sender_province',
        'sender_postal_code',

        // Recipient
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'recipient_city',
        'recipient_province',
        'recipient_postal_code',

        // Package
        'package_type',
        'package_description',
        'actual_weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'volumetric_weight_kg',
        'chargeable_weight_kg',
        'piece_count',

        // Financials
        'shipping_cost',
        'insurance_cost',
        'declared_goods_value',
        'cod_amount',
        'cod_fee',
        'other_fee',
        'discount_amount',
        'total_cost',
        'payment_method',
        'payment_status',

        // Status & Routing
        'current_status',
        'routing_code',
        'special_instructions',

        // POD
        'pod_recipient_name',
        'pod_relationship',
        'pod_delivered_at',
        'pod_notes',
        'pod_photo',
        'pod_signature',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'pod_delivered_at' => 'datetime',
        'actual_weight_kg' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'volumetric_weight_kg' => 'decimal:2',
        'chargeable_weight_kg' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'declared_goods_value' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'other_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function currentBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_courier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(ShipmentTracking::class)->orderBy('occurred_at', 'desc');
    }

    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }

    // Helpers
    public static function generateTrackingNumber(string $prefix = 'CR'): string
    {
        $yearMonth = date('Ym');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $number = "{$prefix}-{$yearMonth}-{$random}";

        // Ensure uniqueness
        while (static::where('tracking_number', $number)->exists()) {
            $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $number = "{$prefix}-{$yearMonth}-{$random}";
        }

        return $number;
    }

    public static function calculateVolumetricWeight(float $length, float $width, float $height, int $divisor = 5000): float
    {
        return round(($length * $width * $height) / max($divisor, 1), 2);
    }

    public function isDelivered(): bool
    {
        return $this->current_status === 'delivered';
    }

    public function isCod(): bool
    {
        return (float) $this->cod_amount > 0;
    }

    public function addTracking(
        string $status,
        string $title,
        ?string $locationCity = null,
        ?string $locationDetails = null,
        ?string $description = null,
        ?int $userId = null,
        ?int $branchId = null,
        ?string $manifestCode = null,
        ?array $metadata = null
    ): ShipmentTracking {
        $tracking = $this->trackings()->create([
            'status' => $status,
            'title' => $title,
            'location_city' => $locationCity ?? ($this->currentBranch?->city ?? $this->originBranch?->city),
            'location_details' => $locationDetails,
            'description' => $description,
            'user_id' => $userId,
            'branch_id' => $branchId ?? $this->current_branch_id,
            'manifest_code' => $manifestCode,
            'occurred_at' => Carbon::now(),
            'metadata' => $metadata,
        ]);

        $this->update([
            'current_status' => $status,
            'current_branch_id' => $branchId ?? $this->current_branch_id,
        ]);

        return $tracking;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->current_status) {
            'booking_created' => 'Booking Created',
            'picked_up' => 'Picked Up',
            'received_at_origin_hub' => 'Received at Origin Hub',
            'manifested_in_transit' => 'In Transit',
            'received_at_transit_hub' => 'At Transit Hub',
            'received_at_dest_branch' => 'Received at Destination Branch',
            'out_for_delivery' => 'With Delivery Courier',
            'delivered' => 'Delivered',
            'failed_attempt' => 'Delivery Attempt Failed',
            'returned_to_sender' => 'Returned to Sender',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->current_status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->current_status) {
            'booking_created' => 'bg-secondary',
            'picked_up' => 'bg-info text-dark',
            'received_at_origin_hub', 'received_at_transit_hub' => 'bg-primary',
            'manifested_in_transit' => 'bg-primary text-white',
            'received_at_dest_branch' => 'bg-indigo text-white',
            'out_for_delivery' => 'bg-warning text-dark',
            'delivered' => 'bg-success',
            'failed_attempt' => 'bg-danger',
            'returned_to_sender' => 'bg-dark',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'unpaid' => 'bg-danger',
            'pending_cod' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
