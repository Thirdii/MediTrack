<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PharmacySeeder::class,
            UserSeeder::class,
            MedicineSeeder::class,
            BatchSeeder::class,
            TransactionSeeder::class,
            ReorderAlertSeeder::class,
        ]);
    }
}
