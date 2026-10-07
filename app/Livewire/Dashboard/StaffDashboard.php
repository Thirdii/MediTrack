<?php

namespace App\Livewire\Dashboard;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\ReorderAlert;
use App\Models\InventoryTransaction;
use App\Services\ReorderPointService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class StaffDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->renderAdminDashboard();
        }

        return $this->renderStaffDashboard($user->pharmacy_id);
    }

    protected function renderStaffDashboard(int $pharmacyId)
    {
        $pharmacy = Pharmacy::find($pharmacyId);

        // KPI calculations
        $totalActiveMedicines = Medicine::forPharmacy($pharmacyId)->active()->count();

        $totalAvailableStock = MedicineBatch::forPharmacy($pharmacyId)
            ->nonExpired()->withStock()->sum('quantity');

        $inventoryValue = MedicineBatch::forPharmacy($pharmacyId)
            ->nonExpired()->withStock()->get()
            ->sum(fn ($b) => $b->quantity * $b->unit_cost);

        $activeAlerts = ReorderAlert::forPharmacy($pharmacyId)->unresolved()->count();

        $nearExpiryBatches = MedicineBatch::forPharmacy($pharmacyId)
            ->nearExpiry(90)->count();

        $expiredBatches = MedicineBatch::forPharmacy($pharmacyId)
            ->expired()->withStock()->count();

        // Recent transactions
        $recentTransactions = InventoryTransaction::forPharmacy($pharmacyId)
            ->with(['medicine', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // Medicines requiring restocking
        $restockAlerts = ReorderAlert::forPharmacy($pharmacyId)
            ->unresolved()
            ->with('medicine')
            ->latest()
            ->take(10)
            ->get();

        // Near-expiry medicines
        $nearExpiryMeds = MedicineBatch::forPharmacy($pharmacyId)
            ->nearExpiry(90)
            ->with('medicine')
            ->orderBy('expiration_date')
            ->take(10)
            ->get();

        // Expired medicines
        $expiredMeds = MedicineBatch::forPharmacy($pharmacyId)
            ->expired()->withStock()
            ->with('medicine')
            ->orderBy('expiration_date', 'desc')
            ->take(10)
            ->get();

        // Chart data
        $chartData = $this->buildChartData($pharmacyId);

        return view('livewire.dashboard.staff-dashboard', compact(
            'pharmacy',
            'totalActiveMedicines',
            'totalAvailableStock',
            'inventoryValue',
            'activeAlerts',
            'nearExpiryBatches',
            'expiredBatches',
            'recentTransactions',
            'restockAlerts',
            'nearExpiryMeds',
            'expiredMeds',
            'chartData',
        ));
    }

    protected function renderAdminDashboard()
    {
        $totalPharmacies = Pharmacy::active()->count();
        $totalStaff = \App\Models\User::staff()->active()->count();
        $totalActiveMedicines = Medicine::active()->count();
        $totalActiveAlerts = ReorderAlert::unresolved()->count();

        $nearExpiryTotal = MedicineBatch::nearExpiry(90)->count();
        $expiredTotal = MedicineBatch::expired()->withStock()->count();

        $combinedInventoryValue = MedicineBatch::nonExpired()->withStock()->get()
            ->sum(fn ($b) => $b->quantity * $b->unit_cost);

        // Per-pharmacy summary
        $pharmacies = Pharmacy::active()
            ->withCount([
                'medicines as active_medicines_count' => fn ($q) => $q->where('is_archived', false),
                'reorderAlerts as active_alerts_count' => fn ($q) => $q->whereIn('status', ['active', 'acknowledged']),
            ])
            ->get();

        // Admin charts — aggregate across all pharmacies
        $chartData = $this->buildChartData(null);

        return view('livewire.dashboard.admin-dashboard', compact(
            'totalPharmacies',
            'totalStaff',
            'totalActiveMedicines',
            'totalActiveAlerts',
            'nearExpiryTotal',
            'expiredTotal',
            'combinedInventoryValue',
            'pharmacies',
            'chartData',
        ));
    }

    /**
     * Build Chart.js data arrays for dashboard charts.
     *
     * @param int|null $pharmacyId  null = all pharmacies (admin)
     */
    protected function buildChartData(?int $pharmacyId): array
    {
        // 1) Stock In vs Stock Out over last 14 days
        $days = 14;
        $labels = [];
        $stockInData = [];
        $stockOutData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('M d');

            $query = InventoryTransaction::whereDate('created_at', $date);
            if ($pharmacyId) {
                $query->where('pharmacy_id', $pharmacyId);
            }

            $stockInData[] = (clone $query)->where('type', 'stock_in')->sum('quantity');
            $stockOutData[] = (clone $query)->where('type', 'stock_out')->sum('quantity');
        }

        // 2) Restocking status summary
        $medicineQuery = Medicine::active();
        if ($pharmacyId) {
            $medicineQuery->forPharmacy($pharmacyId);
        }
        $allMedicines = $medicineQuery->get();

        $ropService = app(ReorderPointService::class);
        $sufficient = 0;
        $restockRequired = 0;
        $outOfStock = 0;

        foreach ($allMedicines as $medicine) {
            $ropData = $ropService->calculate($medicine);
            match ($ropData['status']) {
                'sufficient' => $sufficient++,
                'restock_required' => $restockRequired++,
                'out_of_stock' => $outOfStock++,
                default => null,
            };
        }

        // 3) Expiration status distribution
        $batchQuery = MedicineBatch::withStock();
        if ($pharmacyId) {
            $batchQuery->forPharmacy($pharmacyId);
        }
        $allBatches = $batchQuery->get();

        $expSafe = 0;
        $expWarning = 0;
        $expCritical = 0;
        $expExpired = 0;

        foreach ($allBatches as $batch) {
            match ($batch->getExpirationStatus()) {
                'safe' => $expSafe++,
                'warning' => $expWarning++,
                'critical' => $expCritical++,
                'expired' => $expExpired++,
                default => null,
            };
        }

        // 4) Top 8 medicines by Stock Out volume (last 30 days)
        $topMedsQuery = InventoryTransaction::where('type', 'stock_out')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('medicine_id', DB::raw('SUM(quantity) as total_out'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_out')
            ->limit(8);

        if ($pharmacyId) {
            $topMedsQuery->where('pharmacy_id', $pharmacyId);
        }

        $topMeds = $topMedsQuery->get();
        $topMedLabels = [];
        $topMedValues = [];

        foreach ($topMeds as $row) {
            $med = Medicine::find($row->medicine_id);
            $topMedLabels[] = $med ? $med->generic_name : 'Unknown';
            $topMedValues[] = $row->total_out;
        }

        return [
            'stockMovement' => [
                'labels' => $labels,
                'stockIn' => $stockInData,
                'stockOut' => $stockOutData,
            ],
            'restockingStatus' => [
                'labels' => ['Sufficient', 'Restock Required', 'Out of Stock'],
                'values' => [$sufficient, $restockRequired, $outOfStock],
            ],
            'expirationStatus' => [
                'labels' => ['Safe', 'Warning', 'Critical', 'Expired'],
                'values' => [$expSafe, $expWarning, $expCritical, $expExpired],
            ],
            'topMedicines' => [
                'labels' => $topMedLabels,
                'values' => $topMedValues,
            ],
        ];
    }
}

