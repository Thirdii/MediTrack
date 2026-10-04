<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Pharmacy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
class PharmacyForm extends Component
{
    public ?Pharmacy $pharmacy = null;
    public bool $editing = false;

    public string $name = '';
    public string $address = '';
    public string $contact_number = '';
    public string $email = '';
    public int $expiration_warning_days_medium = 90;
    public int $expiration_warning_days_critical = 30;

    public function mount(?Pharmacy $pharmacy = null): void
    {
        if ($pharmacy && $pharmacy->exists) {
            $this->pharmacy = $pharmacy;
            $this->editing = true;
            $this->name = $pharmacy->name;
            $this->address = $pharmacy->address;
            $this->contact_number = $pharmacy->contact_number;
            $this->email = $pharmacy->email ?? '';
            $this->expiration_warning_days_medium = $pharmacy->expiration_warning_days_medium;
            $this->expiration_warning_days_critical = $pharmacy->expiration_warning_days_critical;
        }
    }

    #[Title('')]
    public function getTitle(): string
    {
        return $this->editing ? "Edit {$this->pharmacy->name}" : 'Create Pharmacy';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'contact_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'expiration_warning_days_medium' => 'required|integer|min:1|max:365',
            'expiration_warning_days_critical' => 'required|integer|min:1|max:365',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            $old = $this->pharmacy->only(array_keys($validated));
            $this->pharmacy->update($validated);

            AuditLog::record(
                action: 'pharmacy_updated',
                description: "Updated pharmacy: {$this->pharmacy->name}",
                entityType: 'pharmacy',
                entityId: $this->pharmacy->id,
                oldValues: $old,
                newValues: $validated,
            );

            session()->flash('success', 'Pharmacy updated successfully.');
        } else {
            $pharmacy = Pharmacy::create($validated);

            AuditLog::record(
                action: 'pharmacy_created',
                description: "Created pharmacy: {$pharmacy->name}",
                entityType: 'pharmacy',
                entityId: $pharmacy->id,
                newValues: $validated,
            );

            session()->flash('success', 'Pharmacy created successfully.');
        }

        $this->redirect(route('pharmacies.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.pharmacy-form')
            ->title($this->editing ? "Edit {$this->pharmacy->name}" : 'Create Pharmacy');
    }
}
