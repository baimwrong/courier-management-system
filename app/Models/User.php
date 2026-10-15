<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'phone',
        'avatar',
        'vehicle_type',
        'vehicle_plate',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shipmentsCreated(): HasMany
    {
        return $this->hasMany(Shipment::class, 'creator_id');
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Shipment::class, 'assigned_courier_id');
    }

    public function manifests(): HasMany
    {
        return $this->hasMany(Manifest::class, 'courier_id');
    }

    // Role helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isBranchManager(): bool
    {
        return in_array($this->role, ['superadmin', 'branch_manager']);
    }

    public function isHubOperator(): bool
    {
        return in_array($this->role, ['superadmin', 'branch_manager', 'hub_operator']);
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['superadmin', 'branch_manager', 'hub_operator', 'cashier', 'courier']);
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'bg-danger',
            'branch_manager' => 'bg-primary',
            'hub_operator' => 'bg-warning text-dark',
            'cashier' => 'bg-info text-dark',
            'courier' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Administrator',
            'branch_manager' => 'Branch Manager',
            'hub_operator' => 'Hub Operator',
            'cashier' => 'Counter Cashier',
            'courier' => 'Delivery Courier',
            'customer' => 'Customer',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }
}
