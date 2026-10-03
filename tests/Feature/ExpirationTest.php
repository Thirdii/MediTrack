<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;

test('expired batch excluded from available stock', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    // Expired batch
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'EXP-001',
        'expiration_date' => now()->subDay(),
        'quantity' => 50,
        'unit_cost' => 10,
        'date_received' => now()->subYear(),
    ]);

    // Valid batch
    MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'VAL-001',
        'expiration_date' => now()->addYear(),
        'quantity' => 30,
        'unit_cost' => 10,
        'date_received' => now(),
    ]);

    // Available stock should only count the non-expired batch
    expect($medicine->available_stock)->toBe(30);
});

test('batch expiration status is correct for expired', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $batch = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'PAST',
        'expiration_date' => now()->subDay(),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subYear(),
    ]);

    expect($batch->getExpirationStatus())->toBe('expired');
    expect($batch->is_expired)->toBeTrue();
});

test('batch expiration status is critical within 30 days', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $batch = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'CRIT',
        'expiration_date' => now()->addDays(15),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subMonths(6),
    ]);

    expect($batch->getExpirationStatus())->toBe('critical');
});

test('batch expiration status is warning within 90 days', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $batch = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'WARN',
        'expiration_date' => now()->addDays(60),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now()->subMonths(3),
    ]);

    expect($batch->getExpirationStatus())->toBe('warning');
});

test('batch expiration status is safe beyond 90 days', function () {
    $pharmacy = Pharmacy::factory()->create();
    $medicine = Medicine::factory()->create(['pharmacy_id' => $pharmacy->id]);

    $batch = MedicineBatch::create([
        'pharmacy_id' => $pharmacy->id,
        'medicine_id' => $medicine->id,
        'batch_number' => 'SAFE',
        'expiration_date' => now()->addDays(180),
        'quantity' => 10,
        'unit_cost' => 5,
        'date_received' => now(),
    ]);

    expect($batch->getExpirationStatus())->toBe('safe');
});
