<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search pharmacies…"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-64">
            <select wire:model.live="status"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <a href="{{ route('pharmacies.create') }}"
            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Pharmacy
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            @if($pharmacies->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-gray-500">No pharmacies found.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Pharmacy</th>
                            <th class="px-6 py-3">Contact</th>
                            <th class="px-6 py-3">Staff</th>
                            <th class="px-6 py-3">Medicines</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pharmacies as $pharmacy)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <p class="font-medium text-gray-900">{{ $pharmacy->name }}</p>
                                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ $pharmacy->address }}</p>
                                </td>
                                <td class="px-6 py-3 text-gray-500 whitespace-nowrap">{{ $pharmacy->contact_number }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $pharmacy->staff_count }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $pharmacy->active_medicines_count }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $pharmacy->active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $pharmacy->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('pharmacies.edit', $pharmacy) }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">Edit</a>
                                    <button wire:click="toggleActive({{ $pharmacy->id }})"
                                        wire:confirm="Are you sure you want to {{ $pharmacy->active ? 'deactivate' : 'activate' }} {{ $pharmacy->name }}?"
                                        class="ml-3 text-sm font-medium {{ $pharmacy->active ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700' }}">
                                        {{ $pharmacy->active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div>{{ $pharmacies->links() }}</div>
</div>
