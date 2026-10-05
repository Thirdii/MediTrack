<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('transactions.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Inventory Adjustment</h2>
    </div>

    <form wire:submit="save" class="bg-white rounded-lg border border-gray-200 p-6 max-w-2xl">
        <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg mb-6 text-sm text-purple-800">
            <strong>Note:</strong> Adjustments are corrections to inventory records. For normal stock movement, use Stock In or Stock Out instead.
        </div>

        <div class="space-y-6">
            <div>
                <label for="medicine_id" class="block text-sm font-medium text-gray-700 mb-1">Medicine <span class="text-red-500">*</span></label>
                <select wire:model.live="medicine_id" id="medicine_id"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Medicine</option>
                    @foreach($medicines as $med)
                        <option value="{{ $med->id }}">{{ $med->display_name }}</option>
                    @endforeach
                </select>
                @error('medicine_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch <span class="text-red-500">*</span></label>
                <select wire:model="batch_id" id="batch_id"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Select Batch</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">
                            {{ $batch->batch_number }} — {{ $batch->quantity }} units, exp {{ $batch->expiration_date->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>
                @error('batch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="direction" class="block text-sm font-medium text-gray-700 mb-1">Direction <span class="text-red-500">*</span></label>
                    <select wire:model="direction" id="direction"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="in">Increase (+)</option>
                        <option value="out">Decrease (-)</option>
                    </select>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input wire:model="quantity" type="number" id="quantity" min="1"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <textarea wire:model="remarks" id="remarks" rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                          placeholder="Explain why this adjustment is needed"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('transactions.index') }}"
               class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors">
                Record Adjustment
            </button>
        </div>
    </form>
</div>
