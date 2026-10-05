<?php

namespace App\Livewire\Inventory;

use App\Models\Medicine;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Stock Out')]
class StockOutForm extends Component
{
    public ?int $medicine_id = null;
    public ?int $quantity = null;
    public string $remarks = '';
    public string $reference_number = '';

    public function mount(): void
    {
        if (request()->has('medicine')) {
            $this->medicine_id = (int) request()->get('medicine');
        }
    }

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'required|string|max:1000',
            'reference_number' => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $medicine = Medicine::findOrFail($this->medicine_id);

        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        $service = app(InventoryService::class);

        try {
            $service->stockOut(
                medicine: $medicine,
                quantity: $this->quantity,
                remarks: $this->remarks,
                referenceNumber: $this->reference_number ?: null,
            );

            session()->flash('success', "Stock Out recorded: {$this->quantity} {$medicine->unit}(s) of {$medicine->generic_name} (FEFO).");
            $this->redirect(route('medicines.show', $medicine), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();

        if ($user->isStaff()) {
            $medicines = Medicine::forPharmacy($user->pharmacy_id)->active()->orderBy('generic_name')->get();
        } else {
            $medicines = Medicine::active()->with('pharmacy')->orderBy('generic_name')->get();
        }

        // Show available stock for selected medicine
        $selectedMedicine = $this->medicine_id ? Medicine::find($this->medicine_id) : null;
        $availableStock = $selectedMedicine ? $selectedMedicine->available_stock : 0;

        // Show batches that FEFO would use
        $fefoBatches = collect();
        if ($selectedMedicine) {
            $fefoBatches = $selectedMedicine->batches()
                ->where('expiration_date', '>', now()->toDateString())
                ->where('quantity', '>', 0)
                ->orderBy('expiration_date')
                ->get();
        }

        return view('livewire.inventory.stock-out-form', [
            'medicines' => $medicines,
            'availableStock' => $availableStock,
            'fefoBatches' => $fefoBatches,
            'selectedMedicine' => $selectedMedicine,
        ]);
    }
}
