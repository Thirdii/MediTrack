<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Transaction History</h2>
            <p class="mt-1 text-sm text-gray-500">View all inventory transactions. Transactions are immutable — use adjustments to correct mistakes.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock-in.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Stock In
            </a>
            <a href="{{ route('stock-out.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                Stock Out
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search Medicine</label>
                <input wire:model.live.debounce.300ms="search" type="text" id="search" placeholder="Name..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="type" class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select wire:model.live="type" id="type" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Types</option>
                    @foreach($transactionTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="medicine" class="block text-xs font-medium text-gray-500 mb-1">Medicine</label>
                <select wire:model.live="medicine" id="medicine" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $med)
                        <option value="{{ $med->id }}">{{ $med->generic_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dateFrom" class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input wire:model.live="dateFrom" type="date" id="dateFrom" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="dateTo" class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input wire:model.live="dateTo" type="date" id="dateTo" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
        </div>
        @if($type || $search || $medicine || $dateFrom || $dateTo)
            <div class="mt-3">
                <button wire:click="clearFilters" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Clear Filters</button>
            </div>
        @endif
    </div>

    {{-- Transaction actions --}}
    <div class="mb-4 flex items-center gap-2 flex-wrap">
        <span class="text-sm text-gray-500">Other transactions:</span>
        <a href="{{ route('adjustment.create') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">Adjustment</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('damaged.create') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">Damaged</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('expired.create') }}" class="text-sm text-red-600 hover:text-red-700 font-medium">Expired</a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($transactions->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">No transactions found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Medicine</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batches</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeColors = [
                                            'stock_in' => 'bg-emerald-100 text-emerald-800',
                                            'stock_out' => 'bg-blue-100 text-blue-800',
                                            'adjustment' => 'bg-purple-100 text-purple-800',
                                            'damaged' => 'bg-orange-100 text-orange-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$txn->type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $txn->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <a href="{{ route('medicines.show', $txn->medicine) }}" class="hover:text-emerald-600">
                                        {{ $txn->medicine->generic_name ?? '—' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium {{ $txn->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $txn->direction === 'in' ? '+' : '-' }}{{ number_format($txn->quantity) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @foreach($txn->lines as $line)
                                        {{ $line->batch->batch_number ?? '?' }}({{ $line->quantity }}){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->user->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="{{ $txn->remarks }}">{{ $txn->remarks ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
