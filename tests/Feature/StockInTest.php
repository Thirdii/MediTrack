<?php

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\InventoryService;

test('stock in creates new batch and transaction', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);
    $transaction = $service->stockIn(
        medicine: $medicine,
        batchNumber: 'BATCH-001',
        expirationDate: now()->addYear()->toDateString(),
        quantity: 100,
        unitCost: 25.50,
        dateReceived: now()->toDateString(),
        remarks: 'Initial stock',
    );

    expect($transaction)->toBeInstanceOf(InventoryTransaction::class);
    expect($transaction->type)->toBe('stock_in');
    expect($transaction->quantity)->toBe(100);

    $batch = MedicineBatch::where('medicine_id', $medicine->id)
        ->where('batch_number', 'BATCH-001')
        ->first();

    expect($batch)->not->toBeNull();
    expect($batch->quantity)->toBe(100);
    expect((float) $batch->unit_cost)->toBe(25.50);
});

test('stock in adds to existing batch', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $batch = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'BATCH-001',
        'expiration_date' => now()->addYear(),
        'quantity' => 50,
        'unit_cost' => 10.00,
        'date_received' => now(),
    ]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);
    $service->stockIn(
        medicine: $medicine,
        batchNumber: 'BATCH-001',
        expirationDate: now()->addYear()->toDateString(),
        quantity: 30,
        unitCost: 10.00,
        dateReceived: now()->toDateString(),
        remarks: 'Restock',
    );

    $batch->refresh();
    expect($batch->quantity)->toBe(80);
});

test('stock in creates transaction record', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);
    $service->stockIn(
        medicine: $medicine,
        batchNumber: 'BATCH-002',
        expirationDate: now()->addYear()->toDateString(),
        quantity: 50,
        unitCost: 15.00,
        dateReceived: now()->toDateString(),
        remarks: 'Test',
    );

    $this->assertDatabaseHas('inventory_transactions', [
        'medicine_id' => $medicine->id,
        'type' => 'stock_in',
        'quantity' => 50,
        'direction' => 'in',
    ]);
});
