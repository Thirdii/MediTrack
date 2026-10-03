<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        return [
            'pharmacy_id' => Pharmacy::factory(),
            'medicine_id' => Medicine::factory(),
            'user_id' => User::factory(),
            'type' => 'stock_in',
            'quantity' => fake()->numberBetween(1, 100),
            'direction' => 'in',
            'remarks' => fake()->sentence(),
            'reference_number' => fake()->optional(0.5)->numerify('REF-#####'),
            'metadata' => null,
        ];
    }

    public function stockIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'stock_in',
            'direction' => 'in',
        ]);
    }

    public function stockOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'stock_out',
            'direction' => 'out',
        ]);
    }

    public function adjustment(string $direction = 'in'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adjustment',
            'direction' => $direction,
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'damaged',
            'direction' => 'out',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expired',
            'direction' => 'out',
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
