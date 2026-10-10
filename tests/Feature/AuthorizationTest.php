<?php

use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\User;

test('staff cannot access pharmacy management', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->actingAs($staff);

    $this->get('/pharmacies')->assertForbidden();
    $this->get('/pharmacies/create')->assertForbidden();
    $this->get('/users')->assertForbidden();
    $this->get('/users/create')->assertForbidden();
    $this->get('/audit-logs')->assertForbidden();
    $this->get('/settings')->assertForbidden();
});

test('admin can access pharmacy management', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'pharmacy_id' => null,
    ]);

    $this->actingAs($admin);

    $this->get('/pharmacies')->assertOk();
    $this->get('/users')->assertOk();
    $this->get('/audit-logs')->assertOk();
    $this->get('/settings')->assertOk();
});

test('staff cannot access another pharmacy medicines', function () {
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

    // Staff A should be forbidden from viewing pharmacy B medicine
    $this->get("/medicines/{$medicineB->id}")->assertForbidden();
});

test('staff can access own pharmacy medicines', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
    ]);

    $this->actingAs($staff);

    $this->get("/medicines/{$medicine->id}")->assertOk();
});

test('admin can access any pharmacy medicine', function () {
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

    $this->get("/medicines/{$medicine->id}")->assertOk();
});
