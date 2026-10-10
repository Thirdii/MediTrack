<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Audit Logs</h2>
            <p class="mt-1 text-sm text-gray-500">System activity log for tracking user and system actions.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            {{-- Search --}}
            <div>
                <label for="audit-search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" id="audit-search"
                       placeholder="Description or user..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            {{-- Action --}}
            <div>
                <label for="audit-action" class="block text-xs font-medium text-gray-500 mb-1">Action</label>
                <select wire:model.live="actionFilter" id="audit-action"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Entity Type --}}
            <div>
                <label for="audit-entity" class="block text-xs font-medium text-gray-500 mb-1">Entity Type</label>
                <select wire:model.live="entityFilter" id="audit-entity"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Entities</option>
                    @foreach($entityTypes as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Pharmacy --}}
            <div>
                <label for="audit-pharmacy" class="block text-xs font-medium text-gray-500 mb-1">Pharmacy</label>
                <select wire:model.live="pharmacyFilter" id="audit-pharmacy"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Pharmacies</option>
                    @foreach($pharmacies as $pharmacy)
                        <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label for="audit-date-from" class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input wire:model.live="dateFrom" type="date" id="audit-date-from"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            {{-- Date To --}}
            <div>
                <label for="audit-date-to" class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input wire:model.live="dateTo" type="date" id="audit-date-to"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
        </div>

        @if($search || $actionFilter || $entityFilter || $pharmacyFilter || $dateFrom || $dateTo)
            <div class="mt-3 flex justify-end">
                <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($logs->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No audit log entries found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Entity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pharmacy</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @switch($log->action)
                                            @case('login') @case('logout') bg-blue-100 text-blue-800 @break
                                            @case('stock_in') bg-emerald-100 text-emerald-800 @break
                                            @case('stock_out') bg-amber-100 text-amber-800 @break
                                            @case('damaged') @case('expired_removal') bg-red-100 text-red-800 @break
                                            @case('adjustment') bg-purple-100 text-purple-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    @if($log->entity_type)
                                        {{ ucfirst($log->entity_type) }}
                                        @if($log->entity_id)
                                            <span class="text-gray-400">#{{ $log->entity_id }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $log->pharmacy?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $log->description }}">
                                    {{ $log->description ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
