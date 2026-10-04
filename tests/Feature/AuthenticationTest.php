<?php

use App\Models\Pharmacy;
use App\Models\User;

test('login page renders', function () {
    $response = $this->get('/login');
    $response->assertOk();
});

test('valid user can login', function () {
    $pharmacy = Pharmacy::factory()->create();
    $user = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
        'password' => 'password',
    ]);

    Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    $this->assertAuthenticated();
});

test('inactive user cannot login', function () {
    $pharmacy = Pharmacy::factory()->create();
    $user = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => false,
        'password' => 'password',
    ]);

    // Attempt login through Livewire component
    Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $this->assertGuest();
});

test('invalid credentials fail login', function () {
    $pharmacy = Pharmacy::factory()->create();
    $user = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $pharmacy = Pharmacy::factory()->create();
    $user = User::factory()->create([
        'pharmacy_id' => $pharmacy->id,
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});

test('guest is redirected to login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});
