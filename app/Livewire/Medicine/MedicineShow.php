<?php

namespace App\Livewire\Medicine;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\InventoryTransaction;
use App\Services\ReorderPointService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MedicineShow extends Component
{
    public Medicine $medicine;

    public function mount(Medicine $medicine): void
    {
        $user = Auth::user();

        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        $this->medicine = $medicine;
    }

    public function render()
    {
        $ropService = app(ReorderPointService::class);
        $ropData = $ropService->calculate($this->medicine);

        // Get batches
        $batches = MedicineBatch::where('medicine_id', $this->medicine->id)
            ->where('pharmacy_id', $this->medicine->pharmacy_id)
            ->orderBy('expiration_date')
            ->get();

        // Get recent transactions
        $transactions = InventoryTransaction::where('medicine_id', $this->medicine->id)
            ->where('pharmacy_id', $this->medicine->pharmacy_id)
            ->with(['user', 'lines.batch'])
            ->latest()
            ->take(20)
            ->get();

        // Get pharmacy expiration settings
        $pharmacy = $this->medicine->pharmacy;
        $criticalDays = $pharmacy->expiration_warning_days_critical ?? 30;
        $warningDays = $pharmacy->expiration_warning_days_medium ?? 90;

        return view('livewire.medicine.medicine-show', [
            'ropData' => $ropData,
            'batches' => $batches,
            'transactions' => $transactions,
            'criticalDays' => $criticalDays,
            'warningDays' => $warningDays,
        ])->title($this->medicine->generic_name);
    }
}
