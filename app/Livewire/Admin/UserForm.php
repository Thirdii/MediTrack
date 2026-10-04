<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserForm extends Component
{
    public ?User $user = null;
    public bool $editing = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'staff';
    public ?int $pharmacy_id = null;

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->editing = true;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->pharmacy_id = $user->pharmacy_id;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'role' => 'required|in:admin,staff',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
        ];

        if (! $this->editing) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'pharmacy_id.required' => 'A pharmacy is required for staff users.',
        ];
    }

    public function save(): void
    {
        // Staff must have a pharmacy
        if ($this->role === 'staff' && empty($this->pharmacy_id)) {
            $this->addError('pharmacy_id', 'A pharmacy is required for staff users.');
            return;
        }

        $validated = $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'pharmacy_id' => $this->role === 'admin' ? null : $this->pharmacy_id,
        ];

        if ($this->editing) {
            if (! empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $old = $this->user->only(['name', 'email', 'role', 'pharmacy_id']);
            $this->user->update($data);

            AuditLog::record(
                action: 'user_updated',
                description: "Updated user: {$this->user->name} ({$this->user->email})",
                entityType: 'user',
                entityId: $this->user->id,
                oldValues: $old,
                newValues: $data,
            );

            session()->flash('success', 'User updated successfully.');
        } else {
            $data['password'] = Hash::make($this->password);
            $data['is_active'] = true;
            $user = User::create($data);

            AuditLog::record(
                action: 'user_created',
                description: "Created user: {$user->name} ({$user->email}) as {$user->role}",
                entityType: 'user',
                entityId: $user->id,
                newValues: array_diff_key($data, ['password' => '']),
            );

            session()->flash('success', 'User created successfully.');
        }

        $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        $pharmacies = Pharmacy::active()->orderBy('name')->get();

        return view('livewire.admin.user-form', [
            'pharmacies' => $pharmacies,
        ])->title($this->editing ? "Edit {$this->user->name}" : 'Create User');
    }
}
