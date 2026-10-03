<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'generic_name',
        'brand_name',
        'category',
        'dosage_strength',
        'dosage_form',
        'unit',
        'description',
        'lead_time_days',
        'safety_stock',
        'initial_average_daily_demand',
        'is_archived',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'safety_stock' => 'integer',
        'initial_average_daily_demand' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public const DOSAGE_FORMS = [
        'Tablet', 'Capsule', 'Syrup', 'Suspension', 'Injection',
        'Cream', 'Ointment', 'Drops', 'Inhaler', 'Gel', 'Patch', 'Other',
    ];

    public const UNITS = [
        'tablet', 'capsule', 'bottle', 'vial', 'ampule',
        'tube', 'box', 'piece', 'sachet', 'strip',
    ];

    public const CATEGORIES = [
        'Analgesics', 'Antibiotics', 'Antihypertensives', 'Antidiabetics',
        'Antihistamines', 'Antipyretics', 'Antacids', 'Bronchodilators',
        'Cardiovascular', 'Dermatological', 'Gastrointestinal',
        'Multivitamins & Supplements', 'Respiratory', 'Anti-inflammatory',
        'Antifungal', 'Antiviral', 'Other',
    ];

    // Relationships

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function reorderAlerts(): HasMany
    {
        return $this->hasMany(ReorderAlert::class);
    }

    // Stock helpers

    /**
     * Get available (non-expired) batches.
     */
    public function availableBatches(): HasMany
    {
        return $this->batches()
            ->where('expiration_date', '>', now()->toDateString())
            ->where('quantity', '>', 0);
    }

    /**
     * Get total available stock (non-expired).
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->availableBatches()->sum('quantity');
    }

    /**
     * Get total inventory value of available stock.
     */
    public function getInventoryValueAttribute(): float
    {
        return $this->availableBatches()
            ->get()
            ->sum(fn ($batch) => $batch->quantity * $batch->unit_cost);
    }

    // Scopes

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Display name combining generic and brand.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->brand_name) {
            return "{$this->generic_name} ({$this->brand_name})";
        }
        return $this->generic_name;
    }
}
