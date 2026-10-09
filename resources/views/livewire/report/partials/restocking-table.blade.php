@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No restocking data found.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Medicine</th>
                <th class="px-6 py-3">Current Stock</th>
                <th class="px-6 py-3">ADD</th>
                <th class="px-6 py-3">Source</th>
                <th class="px-6 py-3">Lead Time</th>
                <th class="px-6 py-3">Safety Stock</th>
                <th class="px-6 py-3">ROP</th>
                <th class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $row['medicine']->generic_name }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $row['current_stock'] }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ number_format($row['average_daily_demand'], 2) }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ ucfirst($row['demand_source']) }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $row['lead_time_days'] }} days</td>
                    <td class="px-6 py-3 text-gray-700">{{ $row['safety_stock'] }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $row['rop'] }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Services\ReorderPointService::statusClass($row['status']) }}">
                            {{ \App\Services\ReorderPointService::statusLabel($row['status']) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
