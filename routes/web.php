<?php

use App\Livewire\Admin\PharmacyForm;
use App\Livewire\Admin\PharmacyIndex;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Alert\AlertIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Dashboard\StaffDashboard;
use App\Livewire\Inventory\AdjustmentForm;
use App\Livewire\Inventory\DamagedForm;
use App\Livewire\Inventory\ExpiredForm;
use App\Livewire\Inventory\StockInForm;
use App\Livewire\Inventory\StockOutForm;
use App\Livewire\Inventory\TransactionIndex;
use App\Livewire\Medicine\MedicineForm;
use App\Livewire\Medicine\MedicineIndex;
use App\Livewire\Medicine\MedicineShow;
use App\Livewire\Notifications\NotificationIndex;
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
    Route::get('dashboard', StaffDashboard::class)->name('dashboard');

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
    Route::get('notifications', NotificationIndex::class)->name('notifications.index');

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

    Route::get('medicines', MedicineIndex::class)->name('medicines.index');
    Route::get('medicines/create', MedicineForm::class)->name('medicines.create');
    Route::get('medicines/{medicine}', MedicineShow::class)->name('medicines.show');
    Route::get('medicines/{medicine}/edit', MedicineForm::class)->name('medicines.edit');

    /*
    |--------------------------------------------------------------------------
    | Inventory Transactions (Phase 6)
    |--------------------------------------------------------------------------
    */

    Route::get('stock-in/create', StockInForm::class)->name('stock-in.create');
    Route::get('stock-out/create', StockOutForm::class)->name('stock-out.create');
    Route::get('adjustment/create', AdjustmentForm::class)->name('adjustment.create');
    Route::get('damaged/create', DamagedForm::class)->name('damaged.create');
    Route::get('expired/create', ExpiredForm::class)->name('expired.create');
    Route::get('transactions', TransactionIndex::class)->name('transactions.index');

    /*
    |--------------------------------------------------------------------------
    | Monitoring (Phase 8)
    |--------------------------------------------------------------------------
    */

    Route::get('alerts', AlertIndex::class)->name('alerts.index');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

});
