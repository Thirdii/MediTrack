<?php

namespace App\Livewire\Medicine;

use App\Models\AuditLog;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
class MedicineForm extends Component
{
    public ?Medicine $medicine = null;
    public bool $editing = false;

    // Form fields
    public string $generic_name = '';
    public string $brand_name = '';
    public string $category = '';
    public string $dosage_strength = '';
    public string $dosage_form = '';
    public string $unit = '';
    public string $description = '';
    public int $lead_time_days = 7;
    public int $safety_stock = 10;
    public ?string $initial_average_daily_demand = null;
    public ?int $pharmacy_id = null;

    public function mount(?Medicine $medicine = null): void
    {
        $user = Auth::user();

        if ($medicine && $medicine->exists) {
            // Edit mode — enforce authorization
            if ($user->isStaff() && $user->pharmacy_id !== $medicine->pharmacy_id) {
                abort(403);
            }

            $this->medicine = $medicine;
            $this->editing = true;
            $this->generic_name = $medicine->generic_name;
            $this->brand_name = $medicine->brand_name ?? '';
            $this->category = $medicine->category;
            $this->dosage_strength = $medicine->dosage_strength ?? '';
            $this->dosage_form = $medicine->dosage_form;
            $this->unit = $medicine->unit;
            $this->description = $medicine->description ?? '';
            $this->lead_time_days = $medicine->lead_time_days;
            $this->safety_stock = $medicine->safety_stock;
            $this->initial_average_daily_demand = $medicine->initial_average_daily_demand;
            $this->pharmacy_id = $medicine->pharmacy_id;
        } else {
            // Create mode
            if ($user->isStaff()) {
                $this->pharmacy_id = $user->pharmacy_id;
            }
        }
    }

    #[Title('')]
    public function getTitle(): string
    {
        return $this->editing ? 'Edit Medicine' : 'Add Medicine';
    }

    public function rules(): array
    {
        return [
            'generic_name' => 'required|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category' => 'required|string|in:' . implode(',', Medicine::CATEGORIES),
            'dosage_strength' => 'nullable|string|max:100',
            'dosage_form' => 'required|string|in:' . implode(',', Medicine::DOSAGE_FORMS),
            'unit' => 'required|string|in:' . implode(',', Medicine::UNITS),
            'description' => 'nullable|string|max:1000',
            'lead_time_days' => 'required|integer|min:0',
            'safety_stock' => 'required|integer|min:0',
            'initial_average_daily_demand' => 'nullable|numeric|min:0',
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Staff can only create medicines for their pharmacy
        if ($user->isStaff() && $this->pharmacy_id != $user->pharmacy_id) {
            abort(403);
        }

        $data = [
            'pharmacy_id' => $this->pharmacy_id,
            'generic_name' => $this->generic_name,
            'brand_name' => $this->brand_name ?: null,
            'category' => $this->category,
            'dosage_strength' => $this->dosage_strength ?: null,
            'dosage_form' => $this->dosage_form,
            'unit' => $this->unit,
            'description' => $this->description ?: null,
            'lead_time_days' => $this->lead_time_days,
            'safety_stock' => $this->safety_stock,
            'initial_average_daily_demand' => $this->initial_average_daily_demand,
        ];

        if ($this->editing) {
            $oldValues = $this->medicine->only(array_keys($data));
            $this->medicine->update($data);

            AuditLog::record(
                action: 'medicine_updated',
                description: "Updated medicine: {$this->generic_name}",
                entityType: 'medicine',
                entityId: $this->medicine->id,
                oldValues: $oldValues,
                newValues: $data,
                pharmacyId: $this->pharmacy_id,
            );

            session()->flash('success', "Medicine \"{$this->generic_name}\" updated successfully.");
        } else {
            $medicine = Medicine::create($data);

            AuditLog::record(
                action: 'medicine_created',
                description: "Created medicine: {$this->generic_name}",
                entityType: 'medicine',
                entityId: $medicine->id,
                newValues: $data,
                pharmacyId: $this->pharmacy_id,
            );

            session()->flash('success', "Medicine \"{$this->generic_name}\" created successfully.");
        }

        $this->redirect(route('medicines.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $pharmacies = $user->isAdmin() ? Pharmacy::active()->orderBy('name')->get() : collect();

        return view('livewire.medicine.medicine-form', [
            'dosageForms' => Medicine::DOSAGE_FORMS,
            'units' => Medicine::UNITS,
            'categories' => Medicine::CATEGORIES,
            'pharmacies' => $pharmacies,
            'isAdmin' => $user->isAdmin(),
        ])->title($this->editing ? 'Edit Medicine' : 'Add Medicine');
    }
}
