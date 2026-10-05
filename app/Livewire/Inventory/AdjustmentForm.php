<?php

namespace App\Livewire\Inventory;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Inventory Adjustment')]
class AdjustmentForm extends Component
{
    public ?int $medicine_id = null;
    public ?int $batch_id = null;
    public ?int $quantity = null;
    public string $direction = 'in';
    public string $remarks = '';

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'batch_id' => 'required|exists:medicine_batches,id',
            'quantity' => 'required|integer|min:1',
            'direction' => 'required|in:in,out',
            'remarks' => 'required|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $medicine = Medicine::findOrFail($this->medicine_id);
        $batch = MedicineBatch::findOrFail($this->batch_id);

        if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
            abort(403);
        }

        $service = app(InventoryService::class);

        try {
            $service->adjustment(
                medicine: $medicine,
                batch: $batch,
                quantity: $this->quantity,
                direction: $this->direction,
                remarks: $this->remarks,
            );

            $dir = $this->direction === 'in' ? '+' : '-';
            session()->flash('success', "Adjustment recorded: {$dir}{$this->quantity} {$medicine->unit}(s) on batch {$batch->batch_number}.");
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

        $batches = collect();
        if ($this->medicine_id) {
            $batches = MedicineBatch::where('medicine_id', $this->medicine_id)
                ->where('quantity', '>', 0)
                ->orderBy('expiration_date')
                ->get();
        }

        return view('livewire.inventory.adjustment-form', [
            'medicines' => $medicines,
            'batches' => $batches,
        ]);
    }
}
