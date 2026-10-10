<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Manage per-pharmacy settings and expiration warning thresholds.</p>
        </div>
    </div>

    {{-- Pharmacy Selector --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <label for="settings-pharmacy-select" class="block text-sm font-medium text-gray-700 mb-2">Select Pharmacy</label>
        <select wire:model.live="selectedPharmacyId" id="settings-pharmacy-select"
                class="w-full sm:w-64 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            @foreach($pharmacies as $pharmacy)
                <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedPharmacyId)
    <form wire:submit="savePharmacySettings">
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
            {{-- Pharmacy Information --}}
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pharmacy Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="settings-name" class="block text-sm font-medium text-gray-700 mb-1">Pharmacy Name</label>
                        <input wire:model="pharmacyName" type="text" id="settings-name" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('pharmacyName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="settings-contact" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input wire:model="pharmacyContact" type="text" id="settings-contact" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('pharmacyContact')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="settings-address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea wire:model="pharmacyAddress" id="settings-address" rows="2" required
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        @error('pharmacyAddress')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="settings-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-gray-400">(optional)</span></label>
                        <input wire:model="pharmacyEmail" type="email" id="settings-email"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('pharmacyEmail')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Expiration Thresholds --}}
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Expiration Warning Thresholds</h3>
                <p class="text-sm text-gray-500 mb-4">Configure when batches are flagged as Warning or Critical based on days until expiration.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
                    <div>
                        <label for="settings-warning-days" class="block text-sm font-medium text-gray-700 mb-1">
                            Warning Threshold
                            <span class="text-gray-400">(days)</span>
                        </label>
                        <input wire:model="expirationWarningDaysMedium" type="number" id="settings-warning-days"
                               min="1" max="365" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <p class="mt-1 text-xs text-gray-400">Batches expiring within this many days show Warning status.</p>
                        @error('expirationWarningDaysMedium')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="settings-critical-days" class="block text-sm font-medium text-gray-700 mb-1">
                            Critical Threshold
                            <span class="text-gray-400">(days)</span>
                        </label>
                        <input wire:model="expirationWarningDaysCritical" type="number" id="settings-critical-days"
                               min="1" max="365" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <p class="mt-1 text-xs text-gray-400">Batches expiring within this many days show Critical status.</p>
                        @error('expirationWarningDaysCritical')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600 max-w-md">
                    <p class="font-medium text-gray-700 mb-1">Status thresholds:</p>
                    <ul class="space-y-1">
                        <li><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Safe</span> — More than {{ $expirationWarningDaysMedium }} days remaining</li>
                        <li><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Warning</span> — {{ $expirationWarningDaysCritical + 1 }}–{{ $expirationWarningDaysMedium }} days remaining</li>
                        <li><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Critical</span> — 0–{{ $expirationWarningDaysCritical }} days remaining</li>
                        <li><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-800 text-white">Expired</span> — Past expiration date</li>
                    </ul>
                </div>
            </div>

            {{-- Save --}}
            <div class="p-6 bg-gray-50 flex justify-end">
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
