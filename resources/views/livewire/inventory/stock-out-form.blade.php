<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('medicines.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Stock Out</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <form wire:submit="save" class="bg-white rounded-lg border border-gray-200 p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Stock Out uses <strong>FEFO (First Expired, First Out)</strong>. Batches nearest expiration are consumed first automatically.
                </p>

                <div class="space-y-6">
                    {{-- Medicine --}}
                    <div>
                        <label for="medicine_id" class="block text-sm font-medium text-gray-700 mb-1">Medicine <span class="text-red-500">*</span></label>
                        <select wire:model.live="medicine_id" id="medicine_id"
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

                    @if($selectedMedicine)
                        <div class="p-3 bg-gray-50 rounded-lg text-sm">
                            <span class="text-gray-500">Available Stock:</span>
                            <span class="font-semibold {{ $availableStock > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ number_format($availableStock) }} {{ $selectedMedicine->unit }}(s)
                            </span>
                        </div>
                    @endif

                    {{-- Quantity --}}
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <input wire:model="quantity" type="number" id="quantity" min="1"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                               placeholder="Enter quantity to issue">
                        @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reference Number --}}
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input wire:model="reference_number" type="text" id="reference_number"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                               placeholder="Optional reference">
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks <span class="text-red-500">*</span></label>
                        <textarea wire:model="remarks" id="remarks" rows="3"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                  placeholder="Reason for stock out"></textarea>
                        @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                    <a href="{{ route('medicines.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors"
                            {{ $availableStock <= 0 ? 'disabled' : '' }}>
                        Record Stock Out
                    </button>
                </div>
            </form>
        </div>

        {{-- FEFO Preview --}}
        <div>
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700">FEFO Batch Order</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Earliest expiration consumed first.</p>
                </div>
                @if($fefoBatches->isEmpty())
                    <div class="px-4 py-6 text-center text-sm text-gray-500">
                        {{ $selectedMedicine ? 'No available batches.' : 'Select a medicine to see batches.' }}
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($fefoBatches as $idx => $batch)
                            <div class="px-4 py-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-xs text-gray-400">#{{ $idx + 1 }}</span>
                                        <span class="text-sm font-medium text-gray-900 ml-1">{{ $batch->batch_number }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $batch->quantity }} units</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Expires: {{ $batch->expiration_date->format('M d, Y') }}
                                    ({{ $batch->days_until_expiration }}d)
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
