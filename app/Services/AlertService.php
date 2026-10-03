<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\ReorderAlert;

class AlertService
{
    protected ReorderPointService $ropService;

    public function __construct(ReorderPointService $ropService)
    {
        $this->ropService = $ropService;
    }

    /**
     * Synchronize restocking alert for a medicine.
     *
     * - If stock <= ROP and no unresolved alert exists, create one.
     * - If stock > ROP and an unresolved alert exists, resolve it.
     */
    public function syncForMedicine(Medicine $medicine): void
    {
        $medicine->refresh();
        $ropData = $this->ropService->calculate($medicine);

        $currentStock = $ropData['current_stock'];
        $rop = $ropData['rop'];

        // Find existing unresolved alert
        $existingAlert = ReorderAlert::where('pharmacy_id', $medicine->pharmacy_id)
            ->where('medicine_id', $medicine->id)
            ->unresolved()
            ->first();

        if ($currentStock <= $rop) {
            // Stock is at or below ROP — need alert
            if (!$existingAlert) {
                ReorderAlert::create([
                    'pharmacy_id' => $medicine->pharmacy_id,
                    'medicine_id' => $medicine->id,
                    'status' => 'active',
                    'current_stock_at_trigger' => $currentStock,
                    'rop_at_trigger' => $rop,
                ]);
            }
        } else {
            // Stock is above ROP — resolve any existing alert
            if ($existingAlert) {
                $existingAlert->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);
            }
        }
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(ReorderAlert $alert, int $userId): void
    {
        if (!$alert->isActive()) {
            return;
        }

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }

    /**
     * Sync alerts for all medicines in a pharmacy.
     */
    public function syncForPharmacy(int $pharmacyId): void
    {
        $medicines = Medicine::where('pharmacy_id', $pharmacyId)
            ->where('is_archived', false)
            ->get();

        foreach ($medicines as $medicine) {
            $this->syncForMedicine($medicine);
        }
    }
}
