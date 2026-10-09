<?php

use App\Models\Pharmacy;
use App\Models\User;

test('staff can access reports page', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->actingAs($staff);

    $response = $this->get('/reports');
    $response->assertOk();
});

test('admin can access reports page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'pharmacy_id' => null,
    ]);

    $this->actingAs($admin);

    $response = $this->get('/reports');
    $response->assertOk();
});

test('guest cannot access reports', function () {
    $response = $this->get('/reports');
    $response->assertRedirect('/login');
});
