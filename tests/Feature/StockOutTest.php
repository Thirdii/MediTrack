<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\InventoryService;

test('stock out uses FEFO - earliest expiring batch first', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    // Batch A expires sooner
    $batchA = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'BATCH-A',
        'expiration_date' => now()->addMonths(2),
        'quantity' => 20,
        'unit_cost' => 10,
        'date_received' => now()->subDays(10),
    ]);

    // Batch B expires later
    $batchB = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'BATCH-B',
        'expiration_date' => now()->addMonths(6),
        'quantity' => 40,
        'unit_cost' => 10,
        'date_received' => now()->subDays(5),
    ]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);
    $service->stockOut(
        medicine: $medicine,
        quantity: 30,
        remarks: 'FEFO test',
    );

    $batchA->refresh();
    $batchB->refresh();

    // Batch A should be fully consumed (20 deducted), then 10 from Batch B
    expect($batchA->quantity)->toBe(0);
    expect($batchB->quantity)->toBe(30);
});

test('stock out spanning multiple batches via FEFO', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'B1',
        'expiration_date' => now()->addMonth(),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subDays(20),
    ]);

    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'B2',
        'expiration_date' => now()->addMonths(3),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subDays(10),
    ]);

    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'B3',
        'expiration_date' => now()->addMonths(6),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subDays(5),
    ]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);
    $transaction = $service->stockOut(
        medicine: $medicine,
        quantity: 25,
        remarks: 'Multi-batch FEFO',
    );

    // Transaction lines should exist for each batch consumed
    expect($transaction->lines)->toHaveCount(3);

    // Verify remaining quantities: B1=0, B2=0, B3=5
    $remaining = MedicineBatch::where('medicine_id', $medicine->id)
        ->orderBy('expiration_date')
        ->pluck('quantity')
        ->toArray();

    expect($remaining)->toBe([0, 0, 5]);
});

test('stock out excludes expired batches', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    // Expired batch
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'EXPIRED',
        'expiration_date' => now()->subDay(),
        'quantity' => 100,
        'unit_cost' => 10,
        'date_received' => now()->subMonths(6),
    ]);

    // Valid batch
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'VALID',
        'expiration_date' => now()->addMonths(6),
        'quantity' => 5,
        'unit_cost' => 10,
        'date_received' => now()->subDays(5),
    ]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);

    // Should use only VALID batch (5 units), not the expired one
    $service->stockOut(
        medicine: $medicine,
        quantity: 5,
        remarks: 'Expired excluded test',
    );

    $valid = MedicineBatch::where('batch_number', 'VALID')->first();
    $expired = MedicineBatch::where('batch_number', 'EXPIRED')->first();

    expect($valid->quantity)->toBe(0);
    expect($expired->quantity)->toBe(100); // Expired batch untouched
});

test('stock out fails with insufficient stock', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'SMALL',
        'expiration_date' => now()->addMonths(6),
        'quantity' => 5,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $this->actingAs($staff);

    $service = app(InventoryService::class);

    expect(fn () => $service->stockOut(
        medicine: $medicine,
        quantity: 50,
        remarks: 'Too much',
    ))->toThrow(\Exception::class, 'Insufficient stock');
});
