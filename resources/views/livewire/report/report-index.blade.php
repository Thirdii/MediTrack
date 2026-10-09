<div class="space-y-6">
    {{-- Report Type Selector & Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Report Type --}}
            <div>
                <label for="reportType" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Report Type</label>
                <select wire:model.live="reportType" id="reportType" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach($this->reportTypes as $key => $label)
                        @if($key === 'audit' && !$isAdmin)
                            @continue
                        @endif
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Pharmacy Filter (admin only) --}}
            @if($isAdmin)
            <div>
                <label for="pharmacyFilter" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pharmacy</label>
                <select wire:model.live="pharmacyFilter" id="pharmacyFilter" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Pharmacies</option>
                    @foreach($pharmacies as $pharmacy)
                        <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Date Range (for stock movement, audit) --}}
            @if(in_array($reportType, ['stock_movement', 'audit']))
            <div>
                <label for="dateFrom" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">From</label>
                <input type="date" wire:model.live="dateFrom" id="dateFrom" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="dateTo" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">To</label>
                <input type="date" wire:model.live="dateTo" id="dateTo" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            @endif

            {{-- Category Filter --}}
            @if(in_array($reportType, ['inventory', 'restocking', 'near_expiry', 'rop']))
            <div>
                <label for="categoryFilter" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Category</label>
                <select wire:model.live="categoryFilter" id="categoryFilter" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Medicine::CATEGORIES as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Transaction Type Filter --}}
            @if($reportType === 'stock_movement')
            <div>
                <label for="transactionTypeFilter" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Transaction Type</label>
                <select wire:model.live="transactionTypeFilter" id="transactionTypeFilter" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Types</option>
                    @foreach(\App\Models\InventoryTransaction::TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Stock Status Filter --}}
            @if($reportType === 'restocking')
            <div>
                <label for="stockStatusFilter" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Stock Status</label>
                <select wire:model.live="stockStatusFilter" id="stockStatusFilter" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="sufficient">Sufficient</option>
                    <option value="restock_required">Restock Required</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            @endif
        </div>

        {{-- Export Buttons --}}
        <div class="mt-4 flex items-center gap-3 border-t border-gray-100 pt-4">
            <button wire:click="exportCsv" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Export CSV
            </button>
            <button wire:click="exportPdf" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                Export PDF
            </button>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                Print
            </button>
        </div>
    </div>

    {{-- Report Content --}}
    <div class="bg-white rounded-xl border border-gray-200 print:border-0 print:shadow-none">
        <div class="px-6 py-4 border-b border-gray-200 print:border-b-2 print:border-gray-400">
            <h2 class="text-base font-semibold text-gray-900">{{ $this->reportTypes[$reportType] ?? 'Report' }}</h2>
        </div>

        <div class="overflow-x-auto">
            @if($reportType === 'inventory')
                @include('livewire.report.partials.inventory-table', ['data' => $reportData])
            @elseif($reportType === 'restocking')
                @include('livewire.report.partials.restocking-table', ['data' => $reportData])
            @elseif($reportType === 'near_expiry')
                @include('livewire.report.partials.near-expiry-table', ['data' => $reportData])
            @elseif($reportType === 'expired')
                @include('livewire.report.partials.expired-table', ['data' => $reportData])
            @elseif($reportType === 'stock_movement')
                @include('livewire.report.partials.stock-movement-table', ['data' => $reportData])
            @elseif($reportType === 'rop')
                @include('livewire.report.partials.rop-table', ['data' => $reportData])
            @elseif($reportType === 'audit')
                @include('livewire.report.partials.audit-table', ['data' => $reportData])
            @endif
        </div>

        @if(method_exists($reportData, 'links'))
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $reportData->links() }}
            </div>
        @endif
    </div>
</div>
