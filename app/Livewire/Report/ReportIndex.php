<?php

namespace App\Livewire\Report;

use App\Models\AuditLog;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Pharmacy;
use App\Models\ReorderAlert;
use App\Services\ReorderPointService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reports')]
class ReportIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $reportType = 'inventory';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $transactionTypeFilter = '';

    #[Url]
    public string $stockStatusFilter = '';

    #[Url]
    public string $expirationStatusFilter = '';

    #[Url]
    public string $pharmacyFilter = '';

    public array $reportTypes = [
        'inventory' => 'Current Inventory',
        'restocking' => 'Restocking / Low Stock',
        'near_expiry' => 'Near-Expiry',
        'expired' => 'Expired Medicines',
        'stock_movement' => 'Stock Movement',
        'rop' => 'ROP Report',
        'audit' => 'User Activity / Audit',
    ];

    public function mount()
    {
        if (!$this->dateFrom) {
            $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        }
        if (!$this->dateTo) {
            $this->dateTo = Carbon::now()->format('Y-m-d');
        }
    }

    public function updatedReportType()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $pharmacyId = $user->isAdmin() ? ($this->pharmacyFilter ?: null) : $user->pharmacy_id;

        $data = match ($this->reportType) {
            'inventory' => $this->getInventoryReport($pharmacyId),
            'restocking' => $this->getRestockingReport($pharmacyId),
            'near_expiry' => $this->getNearExpiryReport($pharmacyId),
            'expired' => $this->getExpiredReport($pharmacyId),
            'stock_movement' => $this->getStockMovementReport($pharmacyId),
            'rop' => $this->getRopReport($pharmacyId),
            'audit' => $this->getAuditReport($pharmacyId),
            default => collect(),
        };

        $pharmacies = $user->isAdmin() ? Pharmacy::active()->get() : collect();

        return view('livewire.report.report-index', [
            'reportData' => $data,
            'pharmacies' => $pharmacies,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    // ─── Report Generators ───

    protected function getInventoryReport(?int $pharmacyId)
    {
        $query = Medicine::active()->with(['batches' => fn ($q) => $q->withStock()]);

        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        return $query->orderBy('generic_name')->paginate(25);
    }

    protected function getRestockingReport(?int $pharmacyId)
    {
        $query = Medicine::active();
        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $medicines = $query->orderBy('generic_name')->get();
        $ropService = app(ReorderPointService::class);
        $results = collect();

        foreach ($medicines as $medicine) {
            $ropData = $ropService->calculate($medicine);
            if ($this->stockStatusFilter && $ropData['status'] !== $this->stockStatusFilter) {
                continue;
            }
            $results->push(array_merge(['medicine' => $medicine], $ropData));
        }

        return $results;
    }

    protected function getNearExpiryReport(?int $pharmacyId)
    {
        $query = MedicineBatch::nearExpiry(90)->with('medicine')->withStock();
        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }
        if ($this->categoryFilter) {
            $query->whereHas('medicine', fn ($q) => $q->where('category', $this->categoryFilter));
        }
        return $query->orderBy('expiration_date')->paginate(25);
    }

    protected function getExpiredReport(?int $pharmacyId)
    {
        $query = MedicineBatch::expired()->withStock()->with('medicine');
        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }
        return $query->orderBy('expiration_date', 'desc')->paginate(25);
    }

    protected function getStockMovementReport(?int $pharmacyId)
    {
        $query = InventoryTransaction::with(['medicine', 'user', 'lines.batch'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }
        if ($this->transactionTypeFilter) {
            $query->where('type', $this->transactionTypeFilter);
        }

        return $query->latest()->paginate(25);
    }

    protected function getRopReport(?int $pharmacyId)
    {
        $query = Medicine::active();
        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $medicines = $query->orderBy('generic_name')->get();
        $ropService = app(ReorderPointService::class);

        return $medicines->map(fn ($med) => array_merge(
            ['medicine' => $med],
            $ropService->calculate($med)
        ));
    }

    protected function getAuditReport(?int $pharmacyId)
    {
        if (!Auth::user()->isAdmin()) {
            return collect();
        }

        $query = AuditLog::with(['user', 'pharmacy'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($pharmacyId) {
            $query->forPharmacy($pharmacyId);
        }

        return $query->latest('created_at')->paginate(25);
    }

    // ─── Export Actions ───

    public function exportCsv()
    {
        $user = Auth::user();
        $pharmacyId = $user->isAdmin() ? ($this->pharmacyFilter ?: null) : $user->pharmacy_id;

        $rows = $this->getCsvRows($pharmacyId);
        $filename = $this->reportType . '_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($file, array_keys($row));
                }
                fputcsv($file, array_values($row));
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $user = Auth::user();
        $pharmacyId = $user->isAdmin() ? ($this->pharmacyFilter ?: null) : $user->pharmacy_id;

        $rows = $this->getCsvRows($pharmacyId);
        $reportTitle = $this->reportTypes[$this->reportType] ?? 'Report';
        $pharmacyName = $pharmacyId ? Pharmacy::find($pharmacyId)?->name : 'All Pharmacies';

        $pdf = Pdf::loadView('reports.pdf-template', [
            'rows' => $rows,
            'reportTitle' => $reportTitle,
            'pharmacyName' => $pharmacyName,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'generatedAt' => now()->format('M d, Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = $this->reportType . '_report_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function getCsvRows(?int $pharmacyId): array
    {
        return match ($this->reportType) {
            'inventory' => $this->inventoryCsvRows($pharmacyId),
            'restocking' => $this->restockingCsvRows($pharmacyId),
            'near_expiry' => $this->nearExpiryCsvRows($pharmacyId),
            'expired' => $this->expiredCsvRows($pharmacyId),
            'stock_movement' => $this->stockMovementCsvRows($pharmacyId),
            'rop' => $this->ropCsvRows($pharmacyId),
            'audit' => $this->auditCsvRows($pharmacyId),
            default => [],
        };
    }

    protected function inventoryCsvRows(?int $pharmacyId): array
    {
        $query = Medicine::active()->with(['batches' => fn ($q) => $q->withStock()]);
        if ($pharmacyId) $query->forPharmacy($pharmacyId);
        if ($this->categoryFilter) $query->where('category', $this->categoryFilter);

        $rows = [];
        foreach ($query->orderBy('generic_name')->get() as $med) {
            foreach ($med->batches as $batch) {
                $rows[] = [
                    'Medicine' => $med->generic_name,
                    'Brand' => $med->brand_name ?? '',
                    'Category' => $med->category,
                    'Batch' => $batch->batch_number,
                    'Expiration' => $batch->expiration_date->format('Y-m-d'),
                    'Quantity' => $batch->quantity,
                    'Unit Cost' => $batch->unit_cost,
                    'Value' => number_format($batch->quantity * $batch->unit_cost, 2),
                    'Expiration Status' => ucfirst($batch->getExpirationStatus()),
                ];
            }
        }
        return $rows;
    }

    protected function restockingCsvRows(?int $pharmacyId): array
    {
        $data = $this->getRestockingReport($pharmacyId);
        return $data->map(fn ($r) => [
            'Medicine' => $r['medicine']->generic_name,
            'Current Stock' => $r['current_stock'],
            'ADD' => $r['average_daily_demand'],
            'Demand Source' => ucfirst($r['demand_source']),
            'Lead Time' => $r['lead_time_days'],
            'Safety Stock' => $r['safety_stock'],
            'ROP' => $r['rop'],
            'Status' => ReorderPointService::statusLabel($r['status']),
        ])->toArray();
    }

    protected function nearExpiryCsvRows(?int $pharmacyId): array
    {
        $query = MedicineBatch::nearExpiry(90)->with('medicine')->withStock();
        if ($pharmacyId) $query->forPharmacy($pharmacyId);
        return $query->orderBy('expiration_date')->get()->map(fn ($b) => [
            'Medicine' => $b->medicine?->generic_name,
            'Batch' => $b->batch_number,
            'Expiration Date' => $b->expiration_date->format('Y-m-d'),
            'Days Remaining' => $b->days_until_expiration,
            'Quantity' => $b->quantity,
            'Status' => ucfirst($b->getExpirationStatus()),
        ])->toArray();
    }

    protected function expiredCsvRows(?int $pharmacyId): array
    {
        $query = MedicineBatch::expired()->withStock()->with('medicine');
        if ($pharmacyId) $query->forPharmacy($pharmacyId);
        return $query->orderBy('expiration_date', 'desc')->get()->map(fn ($b) => [
            'Medicine' => $b->medicine?->generic_name,
            'Batch' => $b->batch_number,
            'Expiration Date' => $b->expiration_date->format('Y-m-d'),
            'Quantity Remaining' => $b->quantity,
            'Unit Cost' => $b->unit_cost,
            'Value' => number_format($b->quantity * $b->unit_cost, 2),
        ])->toArray();
    }

    protected function stockMovementCsvRows(?int $pharmacyId): array
    {
        $query = InventoryTransaction::with(['medicine', 'user', 'lines.batch'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
        if ($pharmacyId) $query->forPharmacy($pharmacyId);
        if ($this->transactionTypeFilter) $query->where('type', $this->transactionTypeFilter);

        return $query->latest()->get()->map(fn ($tx) => [
            'Date' => $tx->created_at->format('Y-m-d H:i'),
            'Type' => $tx->getTypeLabel(),
            'Medicine' => $tx->medicine?->generic_name ?? '',
            'Batch(es)' => $tx->lines->map(fn ($l) => $l->batch?->batch_number)->filter()->implode(', '),
            'Quantity' => ($tx->direction === 'out' ? '-' : '+') . $tx->quantity,
            'User' => $tx->user?->name ?? '',
            'Remarks' => $tx->remarks ?? '',
        ])->toArray();
    }

    protected function ropCsvRows(?int $pharmacyId): array
    {
        $data = $this->getRopReport($pharmacyId);
        return $data->map(fn ($r) => [
            'Medicine' => $r['medicine']->generic_name,
            'ADD' => $r['average_daily_demand'],
            'Demand Source' => ucfirst($r['demand_source']),
            'Lead Time (days)' => $r['lead_time_days'],
            'Safety Stock' => $r['safety_stock'],
            'ROP' => $r['rop'],
            'Formula' => $r['formula_display'],
            'Current Stock' => $r['current_stock'],
            'Status' => ReorderPointService::statusLabel($r['status']),
        ])->toArray();
    }

    protected function auditCsvRows(?int $pharmacyId): array
    {
        if (!Auth::user()->isAdmin()) return [];
        $query = AuditLog::with(['user', 'pharmacy'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
        if ($pharmacyId) $query->forPharmacy($pharmacyId);

        return $query->latest('created_at')->get()->map(fn ($log) => [
            'Date' => $log->created_at->format('Y-m-d H:i'),
            'User' => $log->user?->name ?? 'System',
            'Action' => $log->action,
            'Entity' => $log->entity_type ?? '',
            'Description' => $log->description ?? '',
            'Pharmacy' => $log->pharmacy?->name ?? '',
            'IP' => $log->ip_address ?? '',
        ])->toArray();
    }
}
