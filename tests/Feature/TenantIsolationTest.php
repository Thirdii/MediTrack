<?php

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\User;

test('staff cannot view medicine from another pharmacy via URL', function () {
    $pharmacyA = Pharmacy::factory()->create();
    $pharmacyB = Pharmacy::factory()->create();

    $staffA = User::factory()->create([
        'pharmacy_id' => $pharmacyA->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicineB = Medicine::factory()->create([
        'pharmacy_id' => $pharmacyB->id,
    ]);

    $this->actingAs($staffA);

    $response = $this->get("/medicines/{$medicineB->id}");
    $response->assertForbidden();
});

test('staff cannot edit medicine from another pharmacy', function () {
    $pharmacyA = Pharmacy::factory()->create();
    $pharmacyB = Pharmacy::factory()->create();

    $staffA = User::factory()->create([
        'pharmacy_id' => $pharmacyA->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicineB = Medicine::factory()->create([
        'pharmacy_id' => $pharmacyB->id,
    ]);

    $this->actingAs($staffA);

    $response = $this->get("/medicines/{$medicineB->id}/edit");
    $response->assertForbidden();
});

test('staff medicine index shows only own pharmacy medicines', function () {
    $pharmacyA = Pharmacy::factory()->create();
    $pharmacyB = Pharmacy::factory()->create();

    $staffA = User::factory()->create([
        'pharmacy_id' => $pharmacyA->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    // Medicine in pharmacy A
    Medicine::factory()->create([
        'pharmacy_id' => $pharmacyA->id,
        'generic_name' => 'MedicineA',
    ]);

    // Medicine in pharmacy B
    Medicine::factory()->create([
        'pharmacy_id' => $pharmacyB->id,
        'generic_name' => 'MedicineB',
    ]);

    $this->actingAs($staffA);

    $response = $this->get('/medicines');
    $response->assertOk();
    $response->assertSee('MedicineA');
    $response->assertDontSee('MedicineB');
});

test('admin can view any pharmacy medicine', function () {
    $pharmacy = Pharmacy::factory()->create();
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'pharmacy_id' => null,
    ]);

    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
    ]);

    $this->actingAs($admin);

    $response = $this->get("/medicines/{$medicine->id}");
    $response->assertOk();
});

test('staff transaction index shows only own pharmacy transactions', function () {
    $pharmacyA = Pharmacy::factory()->create();
    $pharmacyB = Pharmacy::factory()->create();

    $staffA = User::factory()->create([
        'pharmacy_id' => $pharmacyA->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicineA = Medicine::factory()->create(['pharmacy_id' => $pharmacyA->id]);
    $medicineB = Medicine::factory()->create(['pharmacy_id' => $pharmacyB->id]);

    $staffB = User::factory()->create([
        'pharmacy_id' => $pharmacyB->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    // Transaction in pharmacy A
    InventoryTransaction::create([
        'pharmacy_id' => $pharmacyA->id,
        'medicine_id' => $medicineA->id,
        'user_id' => $staffA->id,
        'type' => 'stock_in',
        'quantity' => 10,
        'direction' => 'in',
        'remarks' => 'PharmacyA transaction',
    ]);

    // Transaction in pharmacy B
    InventoryTransaction::create([
        'pharmacy_id' => $pharmacyB->id,
        'medicine_id' => $medicineB->id,
        'user_id' => $staffB->id,
        'type' => 'stock_in',
        'quantity' => 10,
        'direction' => 'in',
        'remarks' => 'PharmacyB transaction',
    ]);

    $this->actingAs($staffA);

    $response = $this->get('/transactions');
    $response->assertOk();
    $response->assertSee('PharmacyA transaction');
    $response->assertDontSee('PharmacyB transaction');
});
