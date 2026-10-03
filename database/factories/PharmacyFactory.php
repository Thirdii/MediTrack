<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pharmacy>
 */
class PharmacyFactory extends Factory
{
    protected $model = Pharmacy::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Pharmacy',
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'logo_path' => null,
            'expiration_warning_days_medium' => 90,
            'expiration_warning_days_critical' => 30,
            'active' => true,
        ];
    }

    /**
     * Inactive pharmacy.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
