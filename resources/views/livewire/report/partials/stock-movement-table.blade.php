@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No transactions found for the selected period.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">Type</th>
                <th class="px-6 py-3">Medicine</th>
                <th class="px-6 py-3">Batch(es)</th>
                <th class="px-6 py-3">Qty</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3">Remarks</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $tx)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-gray-500 whitespace-nowrap">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ match($tx->type) {
                                'stock_in' => 'bg-emerald-100 text-emerald-800',
                                'stock_out' => 'bg-blue-100 text-blue-800',
                                'adjustment' => 'bg-purple-100 text-purple-800',
                                'damaged' => 'bg-orange-100 text-orange-800',
                                'expired' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            } }}">
                            {{ $tx->getTypeLabel() }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-900">{{ $tx->medicine?->generic_name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $tx->lines->map(fn ($l) => $l->batch?->batch_number)->filter()->implode(', ') }}</td>
                    <td class="px-6 py-3 font-medium {{ $tx->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $tx->direction === 'in' ? '+' : '-' }}{{ $tx->quantity }}
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $tx->user?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500 max-w-xs truncate">{{ $tx->remarks ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
