<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('medicines.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Stock In</h2>
    </div>

    <form wire:submit="save" class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Medicine --}}
            <div class="md:col-span-2">
                <label for="medicine_id" class="block text-sm font-medium text-gray-700 mb-1">Medicine <span class="text-red-500">*</span></label>
                <select wire:model="medicine_id" id="medicine_id"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Medicine</option>
                    @foreach($medicines as $med)
                        <option value="{{ $med->id }}">
                            {{ $med->generic_name }}{{ $med->brand_name ? " ({$med->brand_name})" : '' }}
                            @if(auth()->user()->isAdmin() && $med->pharmacy)
                                — {{ $med->pharmacy->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('medicine_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Batch Number --}}
            <div>
                <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Batch Number <span class="text-red-500">*</span></label>
                <input wire:model="batch_number" type="text" id="batch_number"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="e.g. BTH-2026-001">
                <p class="mt-1 text-xs text-gray-400">If batch already exists, stock will be added to it.</p>
                @error('batch_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Expiration Date --}}
            <div>
                <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span class="text-red-500">*</span></label>
                <input wire:model="expiration_date" type="date" id="expiration_date"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('expiration_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Quantity --}}
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                <input wire:model="quantity" type="number" id="quantity" min="1"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="Enter quantity received">
                @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Unit Cost --}}
            <div>
                <label for="unit_cost" class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (₱) <span class="text-red-500">*</span></label>
                <input wire:model="unit_cost" type="number" id="unit_cost" min="0" step="0.01"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="0.00">
                @error('unit_cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Date Received --}}
            <div>
                <label for="date_received" class="block text-sm font-medium text-gray-700 mb-1">Date Received <span class="text-red-500">*</span></label>
                <input wire:model="date_received" type="date" id="date_received"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('date_received') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Reference Number --}}
            <div>
                <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                <input wire:model="reference_number" type="text" id="reference_number"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                       placeholder="Optional reference">
                @error('reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Remarks --}}
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks <span class="text-red-500">*</span></label>
                <textarea wire:model="remarks" id="remarks" rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                          placeholder="Describe the stock received"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('medicines.index') }}"
               class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                Record Stock In
            </button>
        </div>
    </form>
</div>
