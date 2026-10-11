<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Services\AlertService;
use App\Services\ReorderPointService;
use App\Services\DemandCalculationService;
use Illuminate\Database\Seeder;

class ReorderAlertSeeder extends Seeder
{
    public function run(): void
    {
        $alertService = app(AlertService::class);

        // Sync alerts for all non-archived medicines
        $medicines = Medicine::where('is_archived', false)->get();

        foreach ($medicines as $medicine) {
            $alertService->syncForMedicine($medicine);
        }

        $alertCount = \App\Models\ReorderAlert::count();
        $this->command->info("Created {$alertCount} reorder alerts.");
    }
}
