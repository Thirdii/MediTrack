<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Medicines</h2>
            <p class="mt-1 text-sm text-gray-500">Manage medicine records for your pharmacy.</p>
        </div>
        <a href="{{ route('medicines.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Medicine
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Search --}}
            <div>
                <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" id="search"
                       placeholder="Generic or brand name..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            {{-- Category --}}
            <div>
                <label for="category" class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select wire:model.live="category" id="category"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Stock Status --}}
            <div>
                <label for="stockStatus" class="block text-xs font-medium text-gray-500 mb-1">Stock Status</label>
                <select wire:model.live="stockStatus" id="stockStatus"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="sufficient">Sufficient</option>
                    <option value="restock_required">Restock Required</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>

            {{-- Archived --}}
            <div>
                <label for="showArchived" class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select wire:model.live="showArchived" id="showArchived"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Active Only</option>
                    <option value="only">Archived Only</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Medicine Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($medicines->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.893 13.393-1.135-1.135a2.252 2.252 0 0 1-.421-.585l-1.08-2.16a.414.414 0 0 0-.663-.107.827.827 0 0 1-.812.21l-1.273-.363a.89.89 0 0 0-.738 1.595l.587.39c.59.395.674 1.23.172 1.732l-.2.2c-.212.212-.33.498-.33.796v.41c0 .409-.11.809-.32 1.158l-1.315 2.191a2.11 2.11 0 0 1-1.81 1.025 1.055 1.055 0 0 1-1.055-1.055v-1.172c0-.92-.56-1.747-1.414-2.089l-.655-.261a2.25 2.25 0 0 1-1.383-2.46l.007-.042a2.25 2.25 0 0 1 .29-.787l.09-.15a2.25 2.25 0 0 1 2.37-1.048l1.178.236a1.125 1.125 0 0 0 1.302-.795l.208-.73a1.125 1.125 0 0 0-.578-1.315l-.665-.332-.091.091a2.25 2.25 0 0 1-1.591.659h-.18a.94.94 0 0 0-.662.274.931.931 0 0 1-1.458-1.137l1.411-2.353a2.25 2.25 0 0 0 .286-.76m11.928 9.869A9 9 0 0 0 8.965 3.525m11.928 9.868A9 9 0 1 1 8.965 3.525" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No medicines found.</p>
                <a href="{{ route('medicines.create') }}" class="mt-3 inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                    Add your first medicine &rarr;
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Medicine</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Form / Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Status</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pharmacy</th>
                            @endif
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($medicines as $medicine)
                            <tr class="hover:bg-gray-50 transition-colors {{ $medicine->is_archived ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('medicines.show', $medicine) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                        {{ $medicine->generic_name }}
                                    </a>
                                    @if($medicine->brand_name)
                                        <p class="text-xs text-gray-500">{{ $medicine->brand_name }}</p>
                                    @endif
                                    @if($medicine->is_archived)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 mt-1">Archived</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $medicine->category }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $medicine->dosage_form }} / {{ $medicine->unit }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">{{ number_format($medicine->available_stock) }}</td>
                                <td class="px-4 py-3">
                                    @php $ropStatus = $medicine->rop_data['status'] ?? 'sufficient'; @endphp
                                    @if($ropStatus === 'out_of_stock')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                                    @elseif($ropStatus === 'restock_required')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Restock Required</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Sufficient</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $medicine->pharmacy->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('medicines.show', $medicine) }}" class="text-gray-400 hover:text-emerald-600" title="View">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('medicines.edit', $medicine) }}" class="text-gray-400 hover:text-emerald-600" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        @if($medicine->is_archived)
                                            @if($confirmRestoreId == $medicine->id)
                                                <div class="flex items-center gap-1">
                                                    <button wire:click="restore({{ $medicine->id }})" class="px-2 py-1 text-xs bg-emerald-600 text-white rounded hover:bg-emerald-700">Restore</button>
                                                    <button wire:click="cancelRestore" class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
                                                </div>
                                            @else
                                                <button wire:click="confirmRestore({{ $medicine->id }})" class="text-gray-400 hover:text-emerald-600" title="Restore">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @else
                                            @if($confirmArchiveId == $medicine->id)
                                                <div class="flex items-center gap-1">
                                                    <button wire:click="archive({{ $medicine->id }})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Archive</button>
                                                    <button wire:click="cancelArchive" class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
                                                </div>
                                            @else
                                                <button wire:click="confirmArchive({{ $medicine->id }})" class="text-gray-400 hover:text-red-500" title="Archive">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>
</div>
