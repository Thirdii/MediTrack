<?php

use App\Livewire\Admin\PharmacyForm;
use App\Livewire\Admin\PharmacyIndex;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Profile\ProfilePage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes (unauthenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    // Redirect root to dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('dashboard', fn () => 'MediTrack modules are being imported.')->name('dashboard');

    // Logout
    Route::post('logout', function () {
        \App\Models\AuditLog::record(
            action: 'logout',
            description: 'User ' . auth()->user()->name . ' logged out',
            pharmacyId: auth()->user()->pharmacy_id,
        );
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Profile
    Route::get('profile', ProfilePage::class)->name('profile');

    // Notifications

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->group(function () {

        // Pharmacies
        Route::get('pharmacies', PharmacyIndex::class)->name('pharmacies.index');
        Route::get('pharmacies/create', PharmacyForm::class)->name('pharmacies.create');
        Route::get('pharmacies/{pharmacy}/edit', PharmacyForm::class)->name('pharmacies.edit');
        Route::get('pharmacies/{pharmacy}', PharmacyForm::class)->name('pharmacies.show');

        // Users
        Route::get('users', UserIndex::class)->name('users.index');
        Route::get('users/create', UserForm::class)->name('users.create');
        Route::get('users/{user}/edit', UserForm::class)->name('users.edit');

        // Audit Logs

        // Settings
    });

    /*
    |--------------------------------------------------------------------------
    | Medicine & Batch Management (Phase 5)
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Inventory Transactions (Phase 6)
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Monitoring (Phase 8)
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

});
