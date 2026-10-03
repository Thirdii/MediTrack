<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\ReorderAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReorderAlert>
 */
class ReorderAlertFactory extends Factory
{
    protected $model = ReorderAlert::class;

    public function definition(): array
    {
        return [
            'pharmacy_id' => Pharmacy::factory(),
            'medicine_id' => Medicine::factory(),
            'status' => 'active',
            'current_stock_at_trigger' => fake()->numberBetween(0, 30),
            'rop_at_trigger' => fake()->numberBetween(40, 80),
            'acknowledged_at' => null,
            'acknowledged_by' => null,
            'resolved_at' => null,
        ];
    }

    public function acknowledged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => User::factory(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function forMedicine(Medicine $medicine): static
    {
        return $this->state(fn (array $attributes) => [
            'medicine_id' => $medicine->id,
            'pharmacy_id' => $medicine->pharmacy_id,
        ]);
    }
}
