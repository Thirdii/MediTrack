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
#[Title('Record Expired Stock')]
class ExpiredForm extends Component
{
    public ?int $medicine_id = null;
    public ?int $batch_id = null;
    public ?int $quantity = null;
    public string $remarks = '';

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'batch_id' => 'required|exists:medicine_batches,id',
            'quantity' => 'required|integer|min:1',
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
            $service->expired(
                medicine: $medicine,
                batch: $batch,
                quantity: $this->quantity,
                remarks: $this->remarks,
            );

            session()->flash('success', "Expired stock recorded: {$this->quantity} {$medicine->unit}(s) from batch {$batch->batch_number}.");
            $this->redirect(route('medicines.show', $medicine), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $medicines = $user->isStaff()
            ? Medicine::forPharmacy($user->pharmacy_id)->active()->orderBy('generic_name')->get()
            : Medicine::active()->with('pharmacy')->orderBy('generic_name')->get();

        // Only show expired batches with stock for expired removal
        $batches = $this->medicine_id
            ? MedicineBatch::where('medicine_id', $this->medicine_id)
                ->where('quantity', '>', 0)
                ->orderBy('expiration_date')
                ->get()
            : collect();

        return view('livewire.inventory.expired-form', compact('medicines', 'batches'));
    }
}
