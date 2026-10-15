<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'import_type',
        'original_filename',
        'file_path',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'errors_log',
    ];

    protected $casts = [
        'errors_log' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->import_type) {
            'shipments' => 'Bulk Shipments Import',
            'status_updates' => 'Bulk Status Scan Updates',
            'tariffs' => 'Bulk Tariffs Matrix Import',
            'branches' => 'Bulk Branch Master Import',
            default => ucfirst(str_replace('_', ' ', $this->import_type)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'processing' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
