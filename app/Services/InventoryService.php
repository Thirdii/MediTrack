<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Process Stock In transaction.
     */
    public function stockIn(
        Medicine $medicine,
        string $batchNumber,
        string $expirationDate,
        int $quantity,
        float $unitCost,
        string $dateReceived,
        string $remarks,
        ?string $referenceNumber = null,
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $medicine, $batchNumber, $expirationDate, $quantity,
            $unitCost, $dateReceived, $remarks, $referenceNumber
        ) {
            // Find or create batch
            $batch = MedicineBatch::firstOrNew([
                'medicine_id' => $medicine->id,
                'batch_number' => $batchNumber,
            ]);

            if ($batch->exists) {
                $batch->quantity += $quantity;
                $batch->save();
            } else {
                $batch->fill([
                    'pharmacy_id' => $medicine->pharmacy_id,
                    'expiration_date' => $expirationDate,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'date_received' => $dateReceived,
                ]);
                $batch->save();
            }

            // Create transaction
            $transaction = InventoryTransaction::create([
                'pharmacy_id' => $medicine->pharmacy_id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'stock_in',
                'quantity' => $quantity,
                'direction' => 'in',
                'remarks' => $remarks,
                'reference_number' => $referenceNumber,
            ]);

            // Create transaction line
            InventoryTransactionLine::create([
                'inventory_transaction_id' => $transaction->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
            ]);

            // Audit log
            AuditLog::record(
                action: 'stock_in',
                description: "Stock In: {$quantity} {$medicine->unit}(s) of {$medicine->generic_name} (Batch: {$batchNumber})",
                entityType: 'medicine',
                entityId: $medicine->id,
                newValues: [
                    'batch_number' => $batchNumber,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                ],
                pharmacyId: $medicine->pharmacy_id,
            );

            // Sync alert — may resolve if stock now > ROP
            $this->alertService->syncForMedicine($medicine);

            return $transaction;
        });
    }

    /**
     * Process Stock Out transaction using FEFO (First Expired, First Out).
     */
    public function stockOut(
        Medicine $medicine,
        int $quantity,
        string $remarks,
        ?string $referenceNumber = null,
    ): InventoryTransaction {
        return DB::transaction(function () use ($medicine, $quantity, $remarks, $referenceNumber) {
            // Get all non-expired batches with stock, ordered by expiration (FEFO)
            $batches = MedicineBatch::where('medicine_id', $medicine->id)
                ->where('pharmacy_id', $medicine->pharmacy_id)
                ->where('expiration_date', '>', now()->toDateString())
                ->where('quantity', '>', 0)
                ->orderBy('expiration_date', 'asc')
                ->lockForUpdate()
                ->get();

            $availableTotal = $batches->sum('quantity');

            if ($availableTotal < $quantity) {
                throw new \Exception(
                    "Insufficient stock. Available: {$availableTotal}, Requested: {$quantity}"
                );
            }

            // Create parent transaction
            $transaction = InventoryTransaction::create([
                'pharmacy_id' => $medicine->pharmacy_id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'stock_out',
                'quantity' => $quantity,
                'direction' => 'out',
                'remarks' => $remarks,
                'reference_number' => $referenceNumber,
            ]);

            // FEFO deduction
            $remaining = $quantity;
            $deductions = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) break;

                $deduct = min($remaining, $batch->quantity);
                $batch->quantity -= $deduct;
                $batch->save();

                InventoryTransactionLine::create([
                    'inventory_transaction_id' => $transaction->id,
                    'batch_id' => $batch->id,
                    'quantity' => $deduct,
                ]);

                $deductions[] = [
                    'batch_number' => $batch->batch_number,
                    'quantity' => $deduct,
                    'expiration_date' => $batch->expiration_date->format('Y-m-d'),
                ];

                $remaining -= $deduct;
            }

            // Audit log
            AuditLog::record(
                action: 'stock_out',
                description: "Stock Out: {$quantity} {$medicine->unit}(s) of {$medicine->generic_name} (FEFO)",
                entityType: 'medicine',
                entityId: $medicine->id,
                newValues: [
                    'quantity' => $quantity,
                    'deductions' => $deductions,
                ],
                pharmacyId: $medicine->pharmacy_id,
            );

            // Sync alert — may trigger if stock now <= ROP
            $this->alertService->syncForMedicine($medicine);

            return $transaction;
        });
    }

    /**
     * Process Adjustment transaction.
     */
    public function adjustment(
        Medicine $medicine,
        MedicineBatch $batch,
        int $quantity,
        string $direction,
        string $remarks,
    ): InventoryTransaction {
        return DB::transaction(function () use ($medicine, $batch, $quantity, $direction, $remarks) {
            $oldQuantity = $batch->quantity;

            if ($direction === 'out') {
                if ($batch->quantity < $quantity) {
                    throw new \Exception(
                        "Cannot adjust below zero. Batch has {$batch->quantity}, adjustment is -{$quantity}"
                    );
                }
                $batch->quantity -= $quantity;
            } else {
                $batch->quantity += $quantity;
            }

            $batch->save();

            $transaction = InventoryTransaction::create([
                'pharmacy_id' => $medicine->pharmacy_id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => $quantity,
                'direction' => $direction,
                'remarks' => $remarks,
            ]);

            InventoryTransactionLine::create([
                'inventory_transaction_id' => $transaction->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
            ]);

            AuditLog::record(
                action: 'adjustment',
                description: "Adjustment ({$direction}): {$quantity} {$medicine->unit}(s) of {$medicine->generic_name} (Batch: {$batch->batch_number})",
                entityType: 'medicine',
                entityId: $medicine->id,
                oldValues: ['quantity' => $oldQuantity],
                newValues: ['quantity' => $batch->quantity, 'direction' => $direction],
                pharmacyId: $medicine->pharmacy_id,
            );

            $this->alertService->syncForMedicine($medicine);

            return $transaction;
        });
    }

    /**
     * Process Damaged stock transaction.
     */
    public function damaged(
        Medicine $medicine,
        MedicineBatch $batch,
        int $quantity,
        string $remarks,
    ): InventoryTransaction {
        return DB::transaction(function () use ($medicine, $batch, $quantity, $remarks) {
            if ($batch->quantity < $quantity) {
                throw new \Exception(
                    "Cannot remove more than batch quantity. Batch has {$batch->quantity}, removing {$quantity}"
                );
            }

            $batch->quantity -= $quantity;
            $batch->save();

            $transaction = InventoryTransaction::create([
                'pharmacy_id' => $medicine->pharmacy_id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'damaged',
                'quantity' => $quantity,
                'direction' => 'out',
                'remarks' => $remarks,
            ]);

            InventoryTransactionLine::create([
                'inventory_transaction_id' => $transaction->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
            ]);

            AuditLog::record(
                action: 'damaged',
                description: "Damaged: {$quantity} {$medicine->unit}(s) of {$medicine->generic_name} (Batch: {$batch->batch_number})",
                entityType: 'medicine',
                entityId: $medicine->id,
                newValues: ['quantity' => $quantity, 'batch_number' => $batch->batch_number],
                pharmacyId: $medicine->pharmacy_id,
            );

            $this->alertService->syncForMedicine($medicine);

            return $transaction;
        });
    }

    /**
     * Process Expired stock removal transaction.
     */
    public function expired(
        Medicine $medicine,
        MedicineBatch $batch,
        int $quantity,
        string $remarks,
    ): InventoryTransaction {
        return DB::transaction(function () use ($medicine, $batch, $quantity, $remarks) {
            if ($batch->quantity < $quantity) {
                throw new \Exception(
                    "Cannot remove more than batch quantity. Batch has {$batch->quantity}, removing {$quantity}"
                );
            }

            $batch->quantity -= $quantity;
            $batch->save();

            $transaction = InventoryTransaction::create([
                'pharmacy_id' => $medicine->pharmacy_id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'expired',
                'quantity' => $quantity,
                'direction' => 'out',
                'remarks' => $remarks,
            ]);

            InventoryTransactionLine::create([
                'inventory_transaction_id' => $transaction->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
            ]);

            AuditLog::record(
                action: 'expired_removal',
                description: "Expired Removal: {$quantity} {$medicine->unit}(s) of {$medicine->generic_name} (Batch: {$batch->batch_number})",
                entityType: 'medicine',
                entityId: $medicine->id,
                newValues: ['quantity' => $quantity, 'batch_number' => $batch->batch_number],
                pharmacyId: $medicine->pharmacy_id,
            );

            return $transaction;
        });
    }
}
