<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Manifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_number',
        'type',
        'origin_branch_id',
        'destination_branch_id',
        'courier_id',
        'created_by',
        'vehicle_plate',
        'vehicle_type',
        'status',
        'total_shipments',
        'total_weight_kg',
        'dispatched_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'total_weight_kg' => 'decimal:2',
    ];

    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }

    public static function generateManifestNumber(string $prefix = 'MNF'): string
    {
        $yearMonth = date('Ym');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $number = "{$prefix}-{$yearMonth}-{$random}";

        while (static::where('manifest_number', $number)->exists()) {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $number = "{$prefix}-{$yearMonth}-{$random}";
        }

        return $number;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'delivery_run_sheet' => 'Courier Delivery Run-Sheet',
            'inter_hub_transit' => 'Inter-Hub Transit Cargo',
            'pickup' => 'First-Mile Pickup Run-Sheet',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'created' => 'bg-secondary',
            'dispatched', 'in_transit' => 'bg-primary',
            'arrived' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
