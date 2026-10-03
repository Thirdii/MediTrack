<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_number',
        'email',
        'logo_path',
        'expiration_warning_days_medium',
        'expiration_warning_days_critical',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expiration_warning_days_medium' => 'integer',
        'expiration_warning_days_critical' => 'integer',
    ];

    // Relationships

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'staff');
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
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

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
