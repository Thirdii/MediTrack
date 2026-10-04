<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Login')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');
            return;
        }

        // Check if user is active
        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->addError('email', 'Your account has been deactivated. Please contact an administrator.');
            return;
        }

        // Audit log
        AuditLog::record(
            action: 'login',
            description: "User {$user->name} logged in",
            pharmacyId: $user->pharmacy_id,
        );

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
