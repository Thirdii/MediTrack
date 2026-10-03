<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'batch_number',
        'expiration_date',
        'quantity',
        'unit_cost',
        'date_received',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'date_received' => 'date',
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
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

    public function transactionLines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class, 'batch_id');
    }

    // Expiration helpers

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date->isPast();
    }

    public function getDaysUntilExpirationAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiration_date->startOfDay(), false);
    }

    /**
     * Get expiration status based on pharmacy settings.
     */
    public function getExpirationStatus(?int $criticalDays = 30, ?int $warningDays = 90): string
    {
        if ($this->is_expired) {
            return 'expired';
        }

        $daysLeft = $this->days_until_expiration;

        if ($daysLeft <= $criticalDays) {
            return 'critical';
        }

        if ($daysLeft <= $warningDays) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Inventory value of this batch.
     */
    public function getInventoryValueAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    // Scopes

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeNonExpired($query)
    {
        return $query->where('expiration_date', '>', now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<=', now()->toDateString());
    }

    public function scopeWithStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeAvailable($query)
    {
        return $query->nonExpired()->withStock();
    }

    public function scopeNearExpiry($query, int $days = 90)
    {
        return $query->nonExpired()
            ->where('expiration_date', '<=', now()->addDays($days)->toDateString())
            ->withStock();
    }
}
