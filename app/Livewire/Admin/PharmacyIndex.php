<?php

namespace App\Livewire\Admin;

use App\Models\Pharmacy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pharmacies')]
class PharmacyIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Pharmacy $pharmacy): void
    {
        $pharmacy->update(['active' => ! $pharmacy->active]);

        \App\Models\AuditLog::record(
            action: $pharmacy->active ? 'pharmacy_activated' : 'pharmacy_deactivated',
            description: ($pharmacy->active ? 'Activated' : 'Deactivated') . " pharmacy: {$pharmacy->name}",
            entityType: 'pharmacy',
            entityId: $pharmacy->id,
        );

        session()->flash('success', "Pharmacy {$pharmacy->name} has been " . ($pharmacy->active ? 'activated' : 'deactivated') . '.');
    }

    public function render()
    {
        $pharmacies = Pharmacy::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status === 'active', fn ($q) => $q->where('active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('active', false))
            ->withCount('staff')
            ->withCount(['medicines as active_medicines_count' => fn ($q) => $q->where('is_archived', false)])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.pharmacy-index', [
            'pharmacies' => $pharmacies,
        ]);
    }
}
