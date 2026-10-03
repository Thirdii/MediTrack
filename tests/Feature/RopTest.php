<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\ReorderAlert;
use App\Models\User;
use App\Services\AlertService;
use App\Services\InventoryService;
use App\Services\ReorderPointService;

test('ROP formula calculates correctly', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    $ropService = app(ReorderPointService::class);
    $result = $ropService->calculate($medicine);

    // ROP = (15 × 4) + 20 = 80
    expect($result['rop'])->toBe(80);
    expect($result['average_daily_demand'])->toBe(15.0);
    expect($result['lead_time_days'])->toBe(4);
    expect($result['safety_stock'])->toBe(20);
});

test('ROP status is sufficient when stock exceeds ROP', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    // ROP = 80. Create batch with 100 units.
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'ABOVE-ROP',
        'expiration_date' => now()->addYear(),
        'quantity' => 100,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $ropService = app(ReorderPointService::class);
    $result = $ropService->calculate($medicine);

    expect($result['status'])->toBe('sufficient');
    expect($result['current_stock'])->toBe(100);
});

test('alert triggers when stock equals ROP', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    // ROP = 80. Create batch with exactly 80 units.
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'AT-ROP',
        'expiration_date' => now()->addYear(),
        'quantity' => 80,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $alertService = app(AlertService::class);
    $alertService->syncForMedicine($medicine);

    // Alert should exist
    $alert = ReorderAlert::where('medicine_id', $medicine->id)
        ->where('pharmacy_id', $pharmacy->id)
        ->first();

    expect($alert)->not->toBeNull();
    expect($alert->status)->toBe('active');
});

test('alert triggers when stock below ROP', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    // ROP = 80. Stock = 50.
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'LOW',
        'expiration_date' => now()->addYear(),
        'quantity' => 50,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $alertService = app(AlertService::class);
    $alertService->syncForMedicine($medicine);

    $alert = ReorderAlert::where('medicine_id', $medicine->id)->first();
    expect($alert)->not->toBeNull();
    expect($alert->status)->toBe('active');
    expect($alert->current_stock_at_trigger)->toBe(50);
    expect($alert->rop_at_trigger)->toBe(80);
});

test('no alert when stock exceeds ROP', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    // ROP = 80. Stock = 100.
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'PLENTY',
        'expiration_date' => now()->addYear(),
        'quantity' => 100,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $alertService = app(AlertService::class);
    $alertService->syncForMedicine($medicine);

    $alert = ReorderAlert::where('medicine_id', $medicine->id)->unresolved()->first();
    expect($alert)->toBeNull();
});

test('alert auto-resolves when stock rises above ROP', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);
    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'lead_time_days' => 4,
        'safety_stock' => 20,
        'initial_average_daily_demand' => 15,
    ]);

    // Start with low stock, create alert
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'RESTOCK-TEST',
        'expiration_date' => now()->addYear(),
        'quantity' => 50,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    $alertService = app(AlertService::class);
    $alertService->syncForMedicine($medicine);

    $alert = ReorderAlert::where('medicine_id', $medicine->id)->first();
    expect($alert->status)->toBe('active');

    // Stock In to bring above ROP (needs > 80)
    $this->actingAs($staff);
    $inventoryService = app(InventoryService::class);
    $inventoryService->stockIn(
        medicine: $medicine,
        batchNumber: 'RESTOCK-TEST',
        expirationDate: now()->addYear()->toDateString(),
        quantity: 50,
        unitCost: 10,
        dateReceived: now()->toDateString(),
        remarks: 'Restock to resolve alert',
    );

    $alert->refresh();
    expect($alert->status)->toBe('resolved');
    expect($alert->resolved_at)->not->toBeNull();
});
