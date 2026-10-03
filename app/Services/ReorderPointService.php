<?php

namespace App\Services;

use App\Models\Medicine;

class ReorderPointService
{
    protected DemandCalculationService $demandService;

    public function __construct(DemandCalculationService $demandService)
    {
        $this->demandService = $demandService;
    }

    /**
     * Calculate the complete ROP data for a medicine.
     *
     * ROP = (Average Daily Demand × Lead Time) + Safety Stock
     *
     * @return array{
     *   average_daily_demand: float,
     *   demand_source: string,
     *   lead_time_days: int,
     *   safety_stock: int,
     *   rop: int,
     *   current_stock: int,
     *   status: string,
     *   formula_display: string
     * }
     */
    public function calculate(Medicine $medicine): array
    {
        $demandData = $this->demandService->calculate($medicine);

        $add = $demandData['value'];
        $leadTime = $medicine->lead_time_days;
        $safetyStock = $medicine->safety_stock;

        // ROP = (ADD × Lead Time) + Safety Stock — round UP
        $rop = (int) ceil(($add * $leadTime) + $safetyStock);

        // Current available stock (non-expired batches)
        $currentStock = $medicine->available_stock;

        // Determine status
        if ($currentStock === 0) {
            $status = 'out_of_stock';
        } elseif ($currentStock <= $rop) {
            $status = 'restock_required';
        } else {
            $status = 'sufficient';
        }

        // Build formula display string
        $formulaDisplay = sprintf(
            '(%s × %d) + %d = %s',
            number_format($add, 2),
            $leadTime,
            $safetyStock,
            $rop
        );

        return [
            'average_daily_demand' => $add,
            'demand_source' => $demandData['source'],
            'demand_total_stock_out' => $demandData['total_stock_out'] ?? 0,
            'demand_lookback_days' => $demandData['lookback_days'] ?? 30,
            'lead_time_days' => $leadTime,
            'safety_stock' => $safetyStock,
            'rop' => $rop,
            'current_stock' => $currentStock,
            'status' => $status,
            'formula_display' => $formulaDisplay,
        ];
    }

    /**
     * Get human-readable status label.
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'sufficient' => 'Sufficient',
            'restock_required' => 'Restock Required',
            'out_of_stock' => 'Out of Stock',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Get CSS class for status badge.
     */
    public static function statusClass(string $status): string
    {
        return match ($status) {
            'sufficient' => 'bg-emerald-100 text-emerald-800',
            'restock_required' => 'bg-amber-100 text-amber-800',
            'out_of_stock' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
