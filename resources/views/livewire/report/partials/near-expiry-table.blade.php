@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No near-expiry batches found.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Medicine</th>
                <th class="px-6 py-3">Batch</th>
                <th class="px-6 py-3">Expiration Date</th>
                <th class="px-6 py-3">Days Left</th>
                <th class="px-6 py-3">Quantity</th>
                <th class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $batch)
                @php $status = $batch->getExpirationStatus(); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $batch->medicine?->generic_name }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->batch_number }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->expiration_date->format('M d, Y') }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->days_until_expiration }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $batch->quantity }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $status === 'critical' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
