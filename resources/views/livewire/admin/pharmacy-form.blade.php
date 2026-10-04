<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('pharmacies.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" wire:navigate>
            ← Back to Pharmacies
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ $editing ? 'Edit Pharmacy' : 'Create Pharmacy' }}</h2>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Pharmacy Name *</label>
                <input wire:model="name" type="text" id="name"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="e.g. MedPlus Pharmacy">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                <textarea wire:model="address" id="address" rows="2"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('address') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="Full address"></textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number *</label>
                    <input wire:model="contact_number" type="text" id="contact_number"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('contact_number') border-red-400 ring-1 ring-red-400 @enderror"
                        placeholder="(02) 8123-4567">
                    @error('contact_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input wire:model="email" type="email" id="email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-red-400 ring-1 ring-red-400 @enderror"
                        placeholder="pharmacy@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-200 pt-5 mt-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Expiration Warning Thresholds</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="expiration_warning_days_medium" class="block text-sm font-medium text-gray-700 mb-1">Warning (days)</label>
                        <input wire:model="expiration_warning_days_medium" type="number" id="expiration_warning_days_medium" min="1" max="365"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('expiration_warning_days_medium') border-red-400 ring-1 ring-red-400 @enderror">
                        <p class="mt-1 text-xs text-gray-400">Batches expiring within this many days show Warning status</p>
                        @error('expiration_warning_days_medium')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="expiration_warning_days_critical" class="block text-sm font-medium text-gray-700 mb-1">Critical (days)</label>
                        <input wire:model="expiration_warning_days_critical" type="number" id="expiration_warning_days_critical" min="1" max="365"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('expiration_warning_days_critical') border-red-400 ring-1 ring-red-400 @enderror">
                        <p class="mt-1 text-xs text-gray-400">Batches expiring within this many days show Critical status</p>
                        @error('expiration_warning_days_critical')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('pharmacies.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium" wire:navigate>Cancel</a>
                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-6 rounded-lg transition-colors">
                    {{ $editing ? 'Update' : 'Create' }} Pharmacy
                </button>
            </div>
        </form>
    </div>
</div>
