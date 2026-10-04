<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Global Admin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@meditrack.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'pharmacy_id' => null,
            'is_active' => true,
        ]);

        // Staff per pharmacy — per spec section 41 demo accounts
        $pharmacies = Pharmacy::all();

        $staffData = [
            1 => [ // Pharmacy 1
                ['name' => 'Maria Santos', 'email' => 'staff1@meditrack.test'],
                ['name' => 'Juan dela Cruz', 'email' => 'staff2@meditrack.test'],
            ],
            2 => [ // Pharmacy 2
                ['name' => 'Ana Reyes', 'email' => 'staff3@meditrack.test'],
                ['name' => 'Carlos Garcia', 'email' => 'staff4@meditrack.test'],
            ],
            3 => [ // Pharmacy 3
                ['name' => 'Rosa Lim', 'email' => 'staff5@meditrack.test'],
                ['name' => 'Pedro Tan', 'email' => 'staff6@meditrack.test'],
            ],
        ];

        foreach ($pharmacies as $index => $pharmacy) {
            $staffIndex = $index + 1;
            if (isset($staffData[$staffIndex])) {
                foreach ($staffData[$staffIndex] as $staff) {
                    User::create([
                        'name' => $staff['name'],
                        'email' => $staff['email'],
                        'password' => Hash::make('password'),
                        'role' => 'staff',
                        'pharmacy_id' => $pharmacy->id,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
