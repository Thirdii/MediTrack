<?php

namespace App\Livewire\Alert;

use App\Models\ReorderAlert;
use App\Services\AlertService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Restocking Alerts')]
class AlertIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function acknowledge(int $alertId): void
    {
        $alert = ReorderAlert::findOrFail($alertId);
        $user = Auth::user();

        if ($user->isStaff() && $user->pharmacy_id !== $alert->pharmacy_id) {
            abort(403);
        }

        $alertService = app(AlertService::class);
        $alertService->acknowledge($alert, $user->id);

        session()->flash('success', 'Alert acknowledged.');
    }

    public function render()
    {
        $user = Auth::user();

        $query = ReorderAlert::with(['medicine', 'pharmacy', 'acknowledgedByUser']);

        if ($user->isStaff()) {
            $query->forPharmacy($user->pharmacy_id);
        }

        if ($this->status === 'active') {
            $query->active();
        } elseif ($this->status === 'unresolved') {
            $query->unresolved();
        } elseif ($this->status === 'resolved') {
            $query->resolved();
        } else {
            // Default: show unresolved
            $query->unresolved();
        }

        $alerts = $query->latest()->paginate(20);

        return view('livewire.alert.alert-index', [
            'alerts' => $alerts,
        ]);
    }
}
