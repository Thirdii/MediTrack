<?php

namespace App\Livewire\AuditLog;

use App\Models\AuditLog;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit Logs')]
class AuditLogIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $actionFilter = '';

    #[Url]
    public string $entityFilter = '';

    #[Url]
    public string $pharmacyFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEntityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPharmacyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'actionFilter', 'entityFilter', 'pharmacyFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $query = AuditLog::with(['user', 'pharmacy']);

        // Search by description or user name
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($uq) {
                      $uq->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->entityFilter) {
            $query->where('entity_type', $this->entityFilter);
        }

        if ($this->pharmacyFilter) {
            $query->where('pharmacy_id', $this->pharmacyFilter);
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom . ' 00:00:00');
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo . ' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->paginate(25);

        // Filter options
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $entityTypes = AuditLog::distinct()->whereNotNull('entity_type')->pluck('entity_type')->sort()->values();
        $pharmacies = Pharmacy::orderBy('name')->get(['id', 'name']);

        return view('livewire.audit-log.audit-log-index', [
            'logs' => $logs,
            'actions' => $actions,
            'entityTypes' => $entityTypes,
            'pharmacies' => $pharmacies,
        ]);
    }
}
