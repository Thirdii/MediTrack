@if($data->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-gray-500">No audit log entries found.</div>
@else
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3">Action</th>
                <th class="px-6 py-3">Entity</th>
                <th class="px-6 py-3">Description</th>
                <th class="px-6 py-3">Pharmacy</th>
                <th class="px-6 py-3">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-3 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $log->entity_type ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-700 max-w-xs truncate">{{ $log->description ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $log->pharmacy?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
