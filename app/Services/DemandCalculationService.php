<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\InventoryTransaction;
use Carbon\Carbon;

class DemandCalculationService
{
    /**
     * Default lookback period in days for calculating Average Daily Demand.
     */
    public const DEFAULT_LOOKBACK_DAYS = 30;

    /**
     * Calculate Average Daily Demand for a medicine.
     *
     * Uses Stock Out history over the lookback period.
     * Falls back to initial_average_daily_demand if insufficient history.
     *
     * @return array{value: float, source: string}
     */
    public function calculate(Medicine $medicine, int $lookbackDays = self::DEFAULT_LOOKBACK_DAYS): array
    {
        $startDate = Carbon::now()->subDays($lookbackDays)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Sum only Stock Out quantities for this medicine within the lookback period
        $totalStockOut = InventoryTransaction::where('medicine_id', $medicine->id)
            ->where('pharmacy_id', $medicine->pharmacy_id)
            ->where('type', 'stock_out')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('quantity');

        // Check if there are any Stock Out transactions at all
        $hasHistory = InventoryTransaction::where('medicine_id', $medicine->id)
            ->where('pharmacy_id', $medicine->pharmacy_id)
            ->where('type', 'stock_out')
            ->where('created_at', '>=', $startDate)
            ->exists();

        if ($hasHistory && $totalStockOut > 0) {
            $add = $totalStockOut / $lookbackDays;
            return [
                'value' => round($add, 2),
                'source' => 'calculated',
                'total_stock_out' => $totalStockOut,
                'lookback_days' => $lookbackDays,
            ];
        }

        // Fallback to manual initial_average_daily_demand
        if ($medicine->initial_average_daily_demand !== null && $medicine->initial_average_daily_demand > 0) {
            return [
                'value' => (float) $medicine->initial_average_daily_demand,
                'source' => 'manual',
                'total_stock_out' => $totalStockOut,
                'lookback_days' => $lookbackDays,
            ];
        }

        // No data available
        return [
            'value' => 0,
            'source' => 'none',
            'total_stock_out' => 0,
            'lookback_days' => $lookbackDays,
        ];
    }
}
