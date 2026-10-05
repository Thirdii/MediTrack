<?php

namespace App\Livewire\Medicine;

use App\Models\Medicine;
use App\Models\AuditLog;
use App\Services\ReorderPointService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Medicines')]
class MedicineIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $stockStatus = '';

    #[Url]
    public string $showArchived = '';

    public string $confirmArchiveId = '';
    public string $confirmRestoreId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedStockStatus(): void
    {
        $this->resetPage();
    }

    public function updatedShowArchived(): void
    {
        $this->resetPage();
    }

    public function confirmArchive(int $id): void
    {
        $this->confirmArchiveId = $id;
    }

    public function cancelArchive(): void
    {
        $this->confirmArchiveId = '';
    }

    public function archive(int $id): void
    {
        $medicine = Medicine::findOrFail($id);
        $user = Auth::user();

        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        // Prevent archiving if available stock exists
        if ($medicine->available_stock > 0) {
            session()->flash('error', 'Cannot archive a medicine with available stock (' . $medicine->available_stock . ' units remaining).');
            $this->confirmArchiveId = '';
            return;
        }

        $medicine->update(['is_archived' => true]);

        AuditLog::record(
            action: 'medicine_archived',
            description: "Archived medicine: {$medicine->generic_name}",
            entityType: 'medicine',
            entityId: $medicine->id,
            pharmacyId: $medicine->pharmacy_id,
        );

        session()->flash('success', "Medicine \"{$medicine->generic_name}\" has been archived.");
        $this->confirmArchiveId = '';
    }

    public function confirmRestore(int $id): void
    {
        $this->confirmRestoreId = $id;
    }

    public function cancelRestore(): void
    {
        $this->confirmRestoreId = '';
    }

    public function restore(int $id): void
    {
        $medicine = Medicine::findOrFail($id);
        $user = Auth::user();

        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        $medicine->update(['is_archived' => false]);

        AuditLog::record(
            action: 'medicine_restored',
            description: "Restored medicine: {$medicine->generic_name}",
            entityType: 'medicine',
            entityId: $medicine->id,
            pharmacyId: $medicine->pharmacy_id,
        );

        session()->flash('success', "Medicine \"{$medicine->generic_name}\" has been restored.");
        $this->confirmRestoreId = '';
    }

    public function render()
    {
        $user = Auth::user();

        $query = Medicine::query()->with('pharmacy');

        // Tenant scoping
        if ($user->isStaff()) {
            $query->forPharmacy($user->pharmacy_id);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('generic_name', 'like', "%{$this->search}%")
                  ->orWhere('brand_name', 'like', "%{$this->search}%");
            });
        }

        // Category filter
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Archived filter
        if ($this->showArchived === 'only') {
            $query->archived();
        } elseif ($this->showArchived !== 'all') {
            $query->active();
        }

        $medicines = $query->orderBy('generic_name')->paginate(15);

        // Compute ROP status for each medicine
        $ropService = app(ReorderPointService::class);
        $medicinesWithRop = $medicines->through(function ($medicine) use ($ropService) {
            $medicine->rop_data = $ropService->calculate($medicine);
            return $medicine;
        });

        return view('livewire.medicine.medicine-index', [
            'medicines' => $medicinesWithRop,
            'categories' => Medicine::CATEGORIES,
        ]);
    }
}
