<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $pharmacies = [
            [
                'name' => 'HealthFirst Pharmacy',
                'address' => '123 Rizal Avenue, Barangay 1, Manila, Metro Manila',
                'contact_number' => '(02) 8123-4567',
                'email' => 'contact@healthfirst.test',
                'expiration_warning_days_medium' => 90,
                'expiration_warning_days_critical' => 30,
                'active' => true,
            ],
            [
                'name' => 'MedCare Drugstore',
                'address' => '456 Quezon Boulevard, Barangay San Antonio, Quezon City',
                'contact_number' => '(02) 8765-4321',
                'email' => 'info@medcare.test',
                'expiration_warning_days_medium' => 90,
                'expiration_warning_days_critical' => 30,
                'active' => true,
            ],
            [
                'name' => 'CurePlus Pharmacy',
                'address' => '789 Osmena Street, Barangay Poblacion, Cebu City, Cebu',
                'contact_number' => '(032) 234-5678',
                'email' => 'cureplus@example.test',
                'expiration_warning_days_medium' => 90,
                'expiration_warning_days_critical' => 30,
                'active' => true,
            ],
        ];

        foreach ($pharmacies as $data) {
            Pharmacy::create($data);
        }
    }
}
