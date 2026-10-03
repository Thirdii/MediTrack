<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicineBatch>
 */
class MedicineBatchFactory extends Factory
{
    protected $model = MedicineBatch::class;

    public function definition(): array
    {
        $medicine = Medicine::factory();

        return [
            'pharmacy_id' => fn (array $attributes) =>
                Medicine::find($attributes['medicine_id'])?->pharmacy_id ?? Pharmacy::factory(),
            'medicine_id' => $medicine,
            'batch_number' => 'BN-' . strtoupper(fake()->bothify('??###')),
            'expiration_date' => fake()->dateTimeBetween('+30 days', '+2 years'),
            'quantity' => fake()->numberBetween(10, 200),
            'unit_cost' => fake()->randomFloat(2, 5, 500),
            'date_received' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Batch for a specific medicine (auto-inherits pharmacy_id).
     */
    public function forMedicine(Medicine $medicine): static
    {
        return $this->state(fn (array $attributes) => [
            'medicine_id' => $medicine->id,
            'pharmacy_id' => $medicine->pharmacy_id,
        ]);
    }

    /**
     * Expired batch.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Critical expiry (within 30 days).
     */
    public function criticalExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }

    /**
     * Warning expiry (31-90 days).
     */
    public function warningExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeBetween('+31 days', '+90 days'),
        ]);
    }

    /**
     * Healthy / safe batch (>90 days out).
     */
    public function safe(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeBetween('+91 days', '+2 years'),
        ]);
    }

    /**
     * Empty batch (zero stock).
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}
