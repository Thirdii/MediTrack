<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'status',
        'current_stock_at_trigger',
        'rop_at_trigger',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
    ];

    protected $casts = [
        'current_stock_at_trigger' => 'integer',
        'rop_at_trigger' => 'integer',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'acknowledged' => 'Acknowledged',
        'resolved' => 'Resolved',
    ];

    // Relationships

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Status helpers

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isUnresolved(): bool
    {
        return in_array($this->status, ['active', 'acknowledged']);
    }

    // Scopes

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['active', 'acknowledged']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
