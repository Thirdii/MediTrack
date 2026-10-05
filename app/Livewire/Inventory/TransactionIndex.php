<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Transactions')]
class TransactionIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $medicine = null;

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mount(): void
    {
        if (request()->has('medicine')) {
            $this->medicine = (int) request()->get('medicine');
        }
    }

    public function updatedType(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedMedicine(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['type', 'search', 'medicine', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = InventoryTransaction::with(['medicine', 'user', 'lines.batch']);

        if ($user->isStaff()) {
            $query->forPharmacy($user->pharmacy_id);
        }

        if ($this->type) {
            $query->ofType($this->type);
        }

        if ($this->medicine) {
            $query->where('medicine_id', $this->medicine);
        }

        if ($this->search) {
            $query->whereHas('medicine', fn ($q) =>
                $q->where('generic_name', 'like', "%{$this->search}%")
                  ->orWhere('brand_name', 'like', "%{$this->search}%")
            );
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo . ' 23:59:59');
        }

        $transactions = $query->latest()->paginate(20);

        // Medicines for filter dropdown
        $medicines = $user->isStaff()
            ? Medicine::forPharmacy($user->pharmacy_id)->orderBy('generic_name')->get()
            : Medicine::orderBy('generic_name')->get();

        return view('livewire.inventory.transaction-index', [
            'transactions' => $transactions,
            'medicines' => $medicines,
            'transactionTypes' => InventoryTransaction::TYPES,
        ]);
    }
}
