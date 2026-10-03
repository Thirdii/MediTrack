<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'user_id',
        'type',
        'quantity',
        'direction',
        'remarks',
        'reference_number',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'metadata' => 'array',
    ];

    public const TYPES = [
        'stock_in' => 'Stock In',
        'stock_out' => 'Stock Out',
        'adjustment' => 'Adjustment',
        'damaged' => 'Damaged',
        'expired' => 'Expired',
    ];

    public const DIRECTIONS = [
        'in' => 'In',
        'out' => 'Out',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class);
    }

    // Helpers

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function isStockIn(): bool
    {
        return $this->type === 'stock_in';
    }

    public function isStockOut(): bool
    {
        return $this->type === 'stock_out';
    }

    // Scopes

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeStockOut($query)
    {
        return $query->where('type', 'stock_out');
    }

    public function scopeStockIn($query)
    {
        return $query->where('type', 'stock_in');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
