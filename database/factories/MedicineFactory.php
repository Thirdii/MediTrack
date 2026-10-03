<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'pharmacy_id' => Pharmacy::factory(),
            'generic_name' => fake()->words(2, true),
            'brand_name' => fake()->optional(0.7)->company(),
            'category' => fake()->randomElement(Medicine::CATEGORIES),
            'dosage_strength' => fake()->randomElement(['100mg', '250mg', '500mg', '5mg/ml', '10mg', '50mg']),
            'dosage_form' => fake()->randomElement(Medicine::DOSAGE_FORMS),
            'unit' => fake()->randomElement(Medicine::UNITS),
            'description' => fake()->optional(0.5)->sentence(),
            'lead_time_days' => fake()->numberBetween(3, 14),
            'safety_stock' => fake()->numberBetween(5, 30),
            'initial_average_daily_demand' => fake()->optional(0.6)->randomFloat(2, 1, 20),
            'is_archived' => false,
        ];
    }

    /**
     * Archived medicine.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }

    /**
     * Medicine with specific pharmacy.
     */
    public function forPharmacy(Pharmacy $pharmacy): static
    {
        return $this->state(fn (array $attributes) => [
            'pharmacy_id' => $pharmacy->id,
        ]);
    }
}
