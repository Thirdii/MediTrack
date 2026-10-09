@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No expired batches with remaining stock.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Medicine</th>
                <th class="px-6 py-3">Batch</th>
                <th class="px-6 py-3">Expiration Date</th>
                <th class="px-6 py-3">Quantity Remaining</th>
                <th class="px-6 py-3">Unit Cost</th>
                <th class="px-6 py-3">Value</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $batch)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $batch->medicine?->generic_name }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->batch_number }}</td>
                    <td class="px-6 py-3 text-red-600">{{ $batch->expiration_date->format('M d, Y') }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->quantity }}</td>
                    <td class="px-6 py-3 text-gray-700">₱{{ number_format($batch->unit_cost, 2) }}</td>
                    <td class="px-6 py-3 text-gray-700">₱{{ number_format($batch->quantity * $batch->unit_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
