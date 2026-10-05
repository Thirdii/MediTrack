<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('medicines.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">{{ $editing ? 'Edit Medicine' : 'Add Medicine' }}</h2>
    </div>

    <form wire:submit="save" class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Pharmacy (admin only) --}}
            @if($isAdmin)
                <div class="md:col-span-2">
                    <label for="pharmacy_id" class="block text-sm font-medium text-gray-700 mb-1">Pharmacy <span class="text-red-500">*</span></label>
                    <select wire:model="pharmacy_id" id="pharmacy_id"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            {{ $editing ? 'disabled' : '' }}>
                        <option value="">Select Pharmacy</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                    @error('pharmacy_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Generic Name --}}
            <div>
                <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1">Generic Name <span class="text-red-500">*</span></label>
                <input wire:model="generic_name" type="text" id="generic_name"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="e.g. Paracetamol">
                @error('generic_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Brand Name --}}
            <div>
                <label for="brand_name" class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                <input wire:model="brand_name" type="text" id="brand_name"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="e.g. Biogesic">
                @error('brand_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Category --}}
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select wire:model="category" id="category"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Dosage Strength --}}
            <div>
                <label for="dosage_strength" class="block text-sm font-medium text-gray-700 mb-1">Dosage Strength</label>
                <input wire:model="dosage_strength" type="text" id="dosage_strength"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="e.g. 500mg">
                @error('dosage_strength') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Dosage Form --}}
            <div>
                <label for="dosage_form" class="block text-sm font-medium text-gray-700 mb-1">Dosage Form <span class="text-red-500">*</span></label>
                <select wire:model="dosage_form" id="dosage_form"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Form</option>
                    @foreach($dosageForms as $form)
                        <option value="{{ $form }}">{{ $form }}</option>
                    @endforeach
                </select>
                @error('dosage_form') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Unit --}}
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                <select wire:model="unit" id="unit"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}">{{ ucfirst($u) }}</option>
                    @endforeach
                </select>
                @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" id="description" rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                          placeholder="Optional description or notes"></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ROP Settings --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reorder Point (ROP) Settings</h3>
            <p class="text-sm text-gray-500 mb-4">
                ROP = (Average Daily Demand × Lead Time) + Safety Stock
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="lead_time_days" class="block text-sm font-medium text-gray-700 mb-1">Lead Time (days) <span class="text-red-500">*</span></label>
                    <input wire:model="lead_time_days" type="number" id="lead_time_days" min="0"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Expected days to replenish stock.</p>
                    @error('lead_time_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="safety_stock" class="block text-sm font-medium text-gray-700 mb-1">Safety Stock (units) <span class="text-red-500">*</span></label>
                    <input wire:model="safety_stock" type="number" id="safety_stock" min="0"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Buffer inventory quantity.</p>
                    @error('safety_stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="initial_average_daily_demand" class="block text-sm font-medium text-gray-700 mb-1">Initial Average Daily Demand</label>
                    <input wire:model="initial_average_daily_demand" type="number" id="initial_average_daily_demand" min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Manual fallback when no Stock Out history exists.</p>
                    @error('initial_average_daily_demand') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('medicines.index') }}"
               class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                {{ $editing ? 'Update Medicine' : 'Create Medicine' }}
            </button>
        </div>
    </form>
</div>
