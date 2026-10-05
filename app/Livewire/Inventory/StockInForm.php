<?php

namespace App\Livewire\Inventory;

use App\Models\Medicine;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Stock In')]
class StockInForm extends Component
{
    public ?int $medicine_id = null;
    public string $batch_number = '';
    public string $expiration_date = '';
    public ?int $quantity = null;
    public ?float $unit_cost = null;
    public string $date_received = '';
    public string $remarks = '';
    public string $reference_number = '';

    public function mount(): void
    {
        $this->date_received = now()->toDateString();

        if (request()->has('medicine')) {
            $this->medicine_id = (int) request()->get('medicine');
        }
    }

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'batch_number' => 'required|string|max:100',
            'expiration_date' => 'required|date|after:today',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'date_received' => 'required|date',
            'remarks' => 'required|string|max:1000',
            'reference_number' => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $medicine = Medicine::findOrFail($this->medicine_id);

        // Tenant check
        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        $service = app(InventoryService::class);

        try {
            $service->stockIn(
                medicine: $medicine,
                batchNumber: $this->batch_number,
                expirationDate: $this->expiration_date,
                quantity: $this->quantity,
                unitCost: $this->unit_cost,
                dateReceived: $this->date_received,
                remarks: $this->remarks,
                referenceNumber: $this->reference_number ?: null,
            );

            session()->flash('success', "Stock In recorded: {$this->quantity} {$medicine->unit}(s) of {$medicine->generic_name}.");
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

        return view('livewire.inventory.stock-in-form', [
            'medicines' => $medicines,
        ]);
    }
}
