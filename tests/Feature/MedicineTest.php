<?php

use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\User;
use Livewire\Livewire;

test('medicine can be created', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->actingAs($staff);

    Livewire::test(\App\Livewire\Medicine\MedicineForm::class)
        ->set('generic_name', 'Amoxicillin')
        ->set('category', 'Antibiotics')
        ->set('dosage_form', 'Capsule')
        ->set('unit', 'capsule')
        ->set('lead_time_days', 7)
        ->set('safety_stock', 10)
        ->call('save');

    $this->assertDatabaseHas('medicines', [
        'pharmacy_id' => $pharmacy->id,
        'generic_name' => 'Amoxicillin',
        'category' => 'Antibiotics',
    ]);
});

test('medicine requires generic name', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->actingAs($staff);

    Livewire::test(\App\Livewire\Medicine\MedicineForm::class)
        ->set('generic_name', '')
        ->set('category', 'Antibiotics')
        ->set('dosage_form', 'Capsule')
        ->set('unit', 'capsule')
        ->call('save')
        ->assertHasErrors(['generic_name']);
});

test('medicine can be archived', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'is_archived' => false,
    ]);

    // Make sure no batches with stock exist
    $medicine->batches()->delete();

    $this->actingAs($staff);

    Livewire::test(\App\Livewire\Medicine\MedicineIndex::class)
        ->call('archive', $medicine->id);

    $medicine->refresh();
    expect($medicine->is_archived)->toBeTrue();
});

test('medicine can be restored from archive', function () {
    $pharmacy = Pharmacy::factory()->create();
    $staff = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $medicine = Medicine::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'is_archived' => true,
    ]);

    $this->actingAs($staff);

    Livewire::test(\App\Livewire\Medicine\MedicineIndex::class)
        ->call('restore', $medicine->id);

    $medicine->refresh();
    expect($medicine->is_archived)->toBeFalse();
});
