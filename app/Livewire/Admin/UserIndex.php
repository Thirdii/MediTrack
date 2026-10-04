<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Pharmacy;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Users')]
class UserIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $pharmacy_id = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(User $user): void
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLog::record(
            action: $user->is_active ? 'user_reactivated' : 'user_deactivated',
            description: ($user->is_active ? 'Reactivated' : 'Deactivated') . " user: {$user->name} ({$user->email})",
            entityType: 'user',
            entityId: $user->id,
        );

        session()->flash('success', "User {$user->name} has been " . ($user->is_active ? 'reactivated' : 'deactivated') . '.');
    }

    public function render()
    {
        $users = User::query()
            ->with('pharmacy')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->pharmacy_id, fn ($q) => $q->where('pharmacy_id', $this->pharmacy_id))
            ->latest()
            ->paginate(15);

        $pharmacies = Pharmacy::active()->orderBy('name')->get();

        return view('livewire.admin.user-index', [
            'users' => $users,
            'pharmacies' => $pharmacies,
        ]);
    }
}
