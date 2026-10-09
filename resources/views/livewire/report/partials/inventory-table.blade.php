@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No inventory data found.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Medicine</th>
                <th class="px-6 py-3">Brand</th>
                <th class="px-6 py-3">Category</th>
                <th class="px-6 py-3">Batches</th>
                <th class="px-6 py-3">Available Stock</th>
                <th class="px-6 py-3">Inventory Value</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $medicine)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $medicine->generic_name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $medicine->brand_name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $medicine->category }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $medicine->batches->count() }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $medicine->available_stock }}</td>
                    <td class="px-6 py-3 text-gray-700">₱{{ number_format($medicine->inventory_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
