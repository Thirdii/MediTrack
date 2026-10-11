<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = Medicine::all();

        foreach ($medicines as $medicine) {
            $batchCount = rand(2, 5);

            for ($i = 1; $i <= $batchCount; $i++) {
                $scenario = $this->pickScenario($i, $batchCount);

                $expirationDate = match ($scenario) {
                    'expired' => Carbon::now()->subDays(rand(1, 60)),
                    'critical' => Carbon::now()->addDays(rand(1, 30)),
                    'warning' => Carbon::now()->addDays(rand(31, 90)),
                    'safe' => Carbon::now()->addDays(rand(120, 365)),
                    default => Carbon::now()->addDays(rand(60, 300)),
                };

                $quantity = match ($scenario) {
                    'expired' => rand(5, 30),
                    'critical' => rand(3, 15),
                    default => rand(10, 200),
                };

                MedicineBatch::create([
                    'pharmacy_id' => $medicine->pharmacy_id,
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BTH-' . strtoupper(substr(md5($medicine->id . $i), 0, 6)),
                    'expiration_date' => $expirationDate,
                    'quantity' => $quantity,
                    'unit_cost' => round(rand(100, 5000) / 100, 2),
                    'date_received' => Carbon::now()->subDays(rand(10, 120)),
                ]);
            }
        }
    }

    /**
     * Ensure mix of expiration scenarios per medicine.
     */
    private function pickScenario(int $batchIndex, int $total): string
    {
        // First batch: always safe/healthy stock
        if ($batchIndex === 1) return 'safe';

        // Guarantee some near-expiry and expired across the dataset
        $scenarios = ['safe', 'safe', 'warning', 'critical', 'expired'];
        return $scenarios[array_rand($scenarios)];
    }
}
