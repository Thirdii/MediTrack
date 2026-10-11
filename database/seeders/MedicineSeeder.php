<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacies = Pharmacy::all();

        if ($pharmacies->isEmpty()) {
            $this->command->warn('No pharmacies found. Run PharmacySeeder first.');
            return;
        }

        // Realistic Filipino pharmacy medicines
        $medicines = [
            // Analgesics
            ['generic_name' => 'Paracetamol', 'brand_name' => 'Biogesic', 'category' => 'Analgesics', 'dosage_form' => 'Tablet', 'dosage_strength' => '500mg', 'unit' => 'tablet', 'lead_time_days' => 5, 'safety_stock' => 50, 'initial_average_daily_demand' => 20],
            ['generic_name' => 'Ibuprofen', 'brand_name' => 'Advil', 'category' => 'Analgesics', 'dosage_form' => 'Tablet', 'dosage_strength' => '200mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 30, 'initial_average_daily_demand' => 10],
            ['generic_name' => 'Mefenamic Acid', 'brand_name' => 'Ponstan', 'category' => 'Analgesics', 'dosage_form' => 'Capsule', 'dosage_strength' => '500mg', 'unit' => 'capsule', 'lead_time_days' => 7, 'safety_stock' => 20, 'initial_average_daily_demand' => 8],

            // Antibiotics
            ['generic_name' => 'Amoxicillin', 'brand_name' => 'Amoxil', 'category' => 'Antibiotics', 'dosage_form' => 'Capsule', 'dosage_strength' => '500mg', 'unit' => 'capsule', 'lead_time_days' => 7, 'safety_stock' => 40, 'initial_average_daily_demand' => 15],
            ['generic_name' => 'Cefalexin', 'brand_name' => 'Cefalin', 'category' => 'Antibiotics', 'dosage_form' => 'Capsule', 'dosage_strength' => '500mg', 'unit' => 'capsule', 'lead_time_days' => 10, 'safety_stock' => 20, 'initial_average_daily_demand' => 5],
            ['generic_name' => 'Azithromycin', 'brand_name' => 'Zithromax', 'category' => 'Antibiotics', 'dosage_form' => 'Tablet', 'dosage_strength' => '500mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 15, 'initial_average_daily_demand' => 4],

            // Antihypertensives
            ['generic_name' => 'Amlodipine', 'brand_name' => 'Norvasc', 'category' => 'Antihypertensives', 'dosage_form' => 'Tablet', 'dosage_strength' => '5mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 30, 'initial_average_daily_demand' => 12],
            ['generic_name' => 'Losartan', 'brand_name' => 'Cozaar', 'category' => 'Antihypertensives', 'dosage_form' => 'Tablet', 'dosage_strength' => '50mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 30, 'initial_average_daily_demand' => 10],

            // Antidiabetics
            ['generic_name' => 'Metformin', 'brand_name' => 'Glucophage', 'category' => 'Antidiabetics', 'dosage_form' => 'Tablet', 'dosage_strength' => '500mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 40, 'initial_average_daily_demand' => 15],

            // Antihistamines
            ['generic_name' => 'Cetirizine', 'brand_name' => 'Zyrtec', 'category' => 'Antihistamines', 'dosage_form' => 'Tablet', 'dosage_strength' => '10mg', 'unit' => 'tablet', 'lead_time_days' => 5, 'safety_stock' => 20, 'initial_average_daily_demand' => 8],
            ['generic_name' => 'Loratadine', 'brand_name' => 'Claritin', 'category' => 'Antihistamines', 'dosage_form' => 'Tablet', 'dosage_strength' => '10mg', 'unit' => 'tablet', 'lead_time_days' => 5, 'safety_stock' => 15, 'initial_average_daily_demand' => 6],

            // Antacids / GI
            ['generic_name' => 'Omeprazole', 'brand_name' => 'Losec', 'category' => 'Gastrointestinal', 'dosage_form' => 'Capsule', 'dosage_strength' => '20mg', 'unit' => 'capsule', 'lead_time_days' => 7, 'safety_stock' => 25, 'initial_average_daily_demand' => 10],
            ['generic_name' => 'Loperamide', 'brand_name' => 'Imodium', 'category' => 'Gastrointestinal', 'dosage_form' => 'Capsule', 'dosage_strength' => '2mg', 'unit' => 'capsule', 'lead_time_days' => 5, 'safety_stock' => 10, 'initial_average_daily_demand' => 3],

            // Respiratory
            ['generic_name' => 'Salbutamol', 'brand_name' => 'Ventolin', 'category' => 'Bronchodilators', 'dosage_form' => 'Inhaler', 'dosage_strength' => '100mcg', 'unit' => 'piece', 'lead_time_days' => 14, 'safety_stock' => 5, 'initial_average_daily_demand' => 2],

            // Anti-inflammatory
            ['generic_name' => 'Diclofenac', 'brand_name' => 'Voltaren', 'category' => 'Anti-inflammatory', 'dosage_form' => 'Tablet', 'dosage_strength' => '50mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 20, 'initial_average_daily_demand' => 6],

            // Dermatological
            ['generic_name' => 'Clotrimazole', 'brand_name' => 'Canesten', 'category' => 'Antifungal', 'dosage_form' => 'Cream', 'dosage_strength' => '1%', 'unit' => 'tube', 'lead_time_days' => 10, 'safety_stock' => 5, 'initial_average_daily_demand' => 2],

            // Vitamins
            ['generic_name' => 'Ascorbic Acid', 'brand_name' => 'Ceelin', 'category' => 'Multivitamins & Supplements', 'dosage_form' => 'Tablet', 'dosage_strength' => '500mg', 'unit' => 'tablet', 'lead_time_days' => 5, 'safety_stock' => 30, 'initial_average_daily_demand' => 12],
            ['generic_name' => 'Multivitamins', 'brand_name' => 'Centrum', 'category' => 'Multivitamins & Supplements', 'dosage_form' => 'Tablet', 'dosage_strength' => null, 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 20, 'initial_average_daily_demand' => 8],

            // Drops
            ['generic_name' => 'Phenylephrine HCl', 'brand_name' => 'Neozep', 'category' => 'Respiratory', 'dosage_form' => 'Drops', 'dosage_strength' => '0.25%', 'unit' => 'bottle', 'lead_time_days' => 7, 'safety_stock' => 10, 'initial_average_daily_demand' => 3],

            // Antipyretics (syrup)
            ['generic_name' => 'Paracetamol Suspension', 'brand_name' => 'Tempra', 'category' => 'Antipyretics', 'dosage_form' => 'Suspension', 'dosage_strength' => '120mg/5ml', 'unit' => 'bottle', 'lead_time_days' => 7, 'safety_stock' => 10, 'initial_average_daily_demand' => 5],

            // Cardiovascular
            ['generic_name' => 'Atorvastatin', 'brand_name' => 'Lipitor', 'category' => 'Cardiovascular', 'dosage_form' => 'Tablet', 'dosage_strength' => '20mg', 'unit' => 'tablet', 'lead_time_days' => 7, 'safety_stock' => 20, 'initial_average_daily_demand' => 8],
        ];

        // Distribute medicines across pharmacies — each pharmacy gets most but not all
        foreach ($pharmacies as $pharmacy) {
            // Shuffle and take 15-20 medicines per pharmacy
            $selected = collect($medicines)->shuffle()->take(rand(17, count($medicines)));

            foreach ($selected as $med) {
                Medicine::create(array_merge($med, [
                    'pharmacy_id' => $pharmacy->id,
                ]));
            }
        }
    }
}
