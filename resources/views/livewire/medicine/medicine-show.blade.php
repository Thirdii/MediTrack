<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('medicines.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $medicine->generic_name }}</h2>
                @if($medicine->brand_name)
                    <p class="text-sm text-gray-500">{{ $medicine->brand_name }}</p>
                @endif
            </div>
            @if($medicine->is_archived)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Archived</span>
            @endif
        </div>
        <a href="{{ route('medicines.edit', $medicine) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
            </svg>
            Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left column: Medicine info + Batches --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Medicine Details --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Medicine Details</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $medicine->category }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Dosage Form</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $medicine->dosage_form }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Dosage Strength</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $medicine->dosage_strength ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Unit</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($medicine->unit) }}</dd>
                    </div>
                    @if($medicine->description)
                        <div class="col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $medicine->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Batches --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Batches</h3>
                </div>
                @if($batches->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-gray-500">
                        No batches found. Stock In to create batches.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batch #</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Unit Cost</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expiration</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($batches as $batch)
                                    @php
                                        $expStatus = $batch->getExpirationStatus($criticalDays, $warningDays);
                                    @endphp
                                    <tr class="hover:bg-gray-50 {{ $expStatus === 'expired' ? 'opacity-60 bg-red-50' : '' }}">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $batch->batch_number }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">{{ number_format($batch->quantity) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600">₱{{ number_format($batch->unit_cost, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $batch->expiration_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3">
                                            @if($expStatus === 'expired')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                            @elseif($expStatus === 'critical')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Critical ({{ $batch->days_until_expiration }}d)</span>
                                            @elseif($expStatus === 'warning')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Warning ({{ $batch->days_until_expiration }}d)</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Safe ({{ $batch->days_until_expiration }}d)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $batch->date_received->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                @if($transactions->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-gray-500">
                        No transactions recorded yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batches</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($transactions as $txn)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->created_at->format('M d, Y H:i') }}</td>
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
                                        <td class="px-4 py-3 text-sm text-right font-medium {{ $txn->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $txn->direction === 'in' ? '+' : '-' }}{{ number_format($txn->quantity) }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            @foreach($txn->lines as $line)
                                                {{ $line->batch->batch_number ?? '?' }}({{ $line->quantity }}){{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->user->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate">{{ $txn->remarks ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right column: ROP Panel + Quick Actions --}}
        <div class="space-y-6">

            {{-- Stock Summary --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Available Stock</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($ropData['current_stock']) }} {{ $medicine->unit }}(s)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Inventory Value</span>
                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($medicine->inventory_value, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        @if($ropData['status'] === 'out_of_stock')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                        @elseif($ropData['status'] === 'restock_required')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Restock Required</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Sufficient</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ROP Breakdown (spec section 19 — visible formula) --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reorder Point (ROP)</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Average Daily Demand</span>
                        <span class="font-medium text-gray-900">
                            {{ number_format($ropData['average_daily_demand'], 2) }} units/day
                            <span class="text-xs {{ $ropData['demand_source'] === 'calculated' ? 'text-emerald-600' : ($ropData['demand_source'] === 'manual' ? 'text-amber-600' : 'text-gray-400') }}">
                                ({{ ucfirst($ropData['demand_source']) }})
                            </span>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Lead Time</span>
                        <span class="font-medium text-gray-900">{{ $ropData['lead_time_days'] }} days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Safety Stock</span>
                        <span class="font-medium text-gray-900">{{ $ropData['safety_stock'] }} units</span>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <div class="bg-gray-50 rounded-lg p-3 font-mono text-xs text-gray-700">
                            <div>ROP = (ADD × Lead Time) + Safety Stock</div>
                            <div class="mt-1">ROP = {{ $ropData['formula_display'] }}</div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="text-gray-500 font-medium">Reorder Point</span>
                        <span class="font-bold text-gray-900">{{ $ropData['rop'] }} units</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Current Stock</span>
                        <span class="font-bold {{ $ropData['current_stock'] <= $ropData['rop'] ? 'text-red-600' : 'text-emerald-600' }}">{{ $ropData['current_stock'] }} units</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('stock-in.create', ['medicine' => $medicine->id]) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Stock In
                    </a>
                    <a href="{{ route('stock-out.create', ['medicine' => $medicine->id]) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        Stock Out
                    </a>
                    <a href="{{ route('transactions.index', ['medicine' => $medicine->id]) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        View All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
