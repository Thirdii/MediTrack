<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use App\Models\Pharmacy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class SettingsIndex extends Component
{
    public ?int $selectedPharmacyId = null;

    // Pharmacy fields
    public string $pharmacyName = '';
    public string $pharmacyAddress = '';
    public string $pharmacyContact = '';
    public string $pharmacyEmail = '';
    public int $expirationWarningDaysMedium = 90;
    public int $expirationWarningDaysCritical = 30;

    public function mount(): void
    {
        // Default to first pharmacy
        $first = Pharmacy::orderBy('name')->first();
        if ($first) {
            $this->selectedPharmacyId = $first->id;
            $this->loadPharmacy();
        }
    }

    public function updatedSelectedPharmacyId(): void
    {
        $this->loadPharmacy();
    }

    protected function loadPharmacy(): void
    {
        if (!$this->selectedPharmacyId) {
            return;
        }

        $pharmacy = Pharmacy::find($this->selectedPharmacyId);
        if (!$pharmacy) {
            return;
        }

        $this->pharmacyName = $pharmacy->name;
        $this->pharmacyAddress = $pharmacy->address;
        $this->pharmacyContact = $pharmacy->contact_number;
        $this->pharmacyEmail = $pharmacy->email ?? '';
        $this->expirationWarningDaysMedium = $pharmacy->expiration_warning_days_medium;
        $this->expirationWarningDaysCritical = $pharmacy->expiration_warning_days_critical;
    }

    public function savePharmacySettings(): void
    {
        $this->validate([
            'pharmacyName' => 'required|string|max:255',
            'pharmacyAddress' => 'required|string',
            'pharmacyContact' => 'required|string|max:50',
            'pharmacyEmail' => 'nullable|email|max:255',
            'expirationWarningDaysMedium' => 'required|integer|min:1|max:365',
            'expirationWarningDaysCritical' => 'required|integer|min:1|max:365',
        ]);

        if ($this->expirationWarningDaysCritical >= $this->expirationWarningDaysMedium) {
            $this->addError('expirationWarningDaysCritical', 'Critical threshold must be less than warning threshold.');
            return;
        }

        $pharmacy = Pharmacy::findOrFail($this->selectedPharmacyId);

        $oldValues = [
            'name' => $pharmacy->name,
            'address' => $pharmacy->address,
            'contact_number' => $pharmacy->contact_number,
            'email' => $pharmacy->email,
            'expiration_warning_days_medium' => $pharmacy->expiration_warning_days_medium,
            'expiration_warning_days_critical' => $pharmacy->expiration_warning_days_critical,
        ];

        $pharmacy->update([
            'name' => $this->pharmacyName,
            'address' => $this->pharmacyAddress,
            'contact_number' => $this->pharmacyContact,
            'email' => $this->pharmacyEmail ?: null,
            'expiration_warning_days_medium' => $this->expirationWarningDaysMedium,
            'expiration_warning_days_critical' => $this->expirationWarningDaysCritical,
        ]);

        AuditLog::record(
            action: 'settings_update',
            description: "Updated settings for pharmacy: {$pharmacy->name}",
            entityType: 'pharmacy',
            entityId: $pharmacy->id,
            oldValues: $oldValues,
            newValues: [
                'name' => $this->pharmacyName,
                'address' => $this->pharmacyAddress,
                'contact_number' => $this->pharmacyContact,
                'email' => $this->pharmacyEmail ?: null,
                'expiration_warning_days_medium' => $this->expirationWarningDaysMedium,
                'expiration_warning_days_critical' => $this->expirationWarningDaysCritical,
            ],
            pharmacyId: $pharmacy->id,
        );

        session()->flash('success', 'Settings saved successfully.');
    }

    public function render()
    {
        $pharmacies = Pharmacy::orderBy('name')->get(['id', 'name']);

        return view('livewire.settings.settings-index', [
            'pharmacies' => $pharmacies,
        ]);
    }
}
