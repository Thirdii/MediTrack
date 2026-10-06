<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Restocking Alerts</h2>
            <p class="mt-1 text-sm text-gray-500">Medicines where stock is at or below the Reorder Point.</p>
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="flex items-center gap-2 mb-6">
        <button wire:click="$set('status', '')"
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $status === '' ? 'bg-emerald-100 text-emerald-800' : 'text-gray-600 hover:bg-gray-100' }}">
            Unresolved
        </button>
        <button wire:click="$set('status', 'active')"
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $status === 'active' ? 'bg-red-100 text-red-800' : 'text-gray-600 hover:bg-gray-100' }}">
            Active
        </button>
        <button wire:click="$set('status', 'resolved')"
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $status === 'resolved' ? 'bg-gray-200 text-gray-800' : 'text-gray-600 hover:bg-gray-100' }}">
            Resolved
        </button>
    </div>

    {{-- Alerts --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($alerts->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No {{ $status ?: 'unresolved' }} restocking alerts.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Medicine</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pharmacy</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Stock at Trigger</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">ROP at Trigger</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Acknowledged</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($alerts as $alert)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('medicines.show', $alert->medicine) }}" class="font-medium text-gray-900 hover:text-emerald-600">
                                        {{ $alert->medicine->generic_name ?? '—' }}
                                    </a>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $alert->pharmacy->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3">
                                    @if($alert->status === 'active')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Active</span>
                                    @elseif($alert->status === 'acknowledged')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Acknowledged</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Resolved</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $alert->current_stock_at_trigger }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $alert->rop_at_trigger }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $alert->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if($alert->acknowledged_at)
                                        {{ $alert->acknowledged_at->format('M d, Y H:i') }}
                                        <br><span class="text-xs text-gray-400">by {{ $alert->acknowledgedByUser->name ?? '—' }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($alert->isActive())
                                        <button wire:click="acknowledge({{ $alert->id }})"
                                                class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                                            Acknowledge
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
