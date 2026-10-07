<div class="space-y-6">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Medicines</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalActiveMedicines) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Available Stock</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalAvailableStock) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Inventory Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">₱{{ number_format($inventoryValue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Alerts</p>
            <p class="mt-2 text-2xl font-bold {{ $activeAlerts > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $activeAlerts }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Near-Expiry</p>
            <p class="mt-2 text-2xl font-bold {{ $nearExpiryBatches > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $nearExpiryBatches }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Expired</p>
            <p class="mt-2 text-2xl font-bold {{ $expiredBatches > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $expiredBatches }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Stock Movement Chart --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Stock In vs Stock Out (Last 14 Days)</h3>
            <div class="relative h-64">
                <canvas id="stockMovementChart"></canvas>
            </div>
        </div>

        {{-- Top Medicines Chart --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Most Issued Medicines (Last 30 Days)</h3>
            <div class="relative h-64">
                <canvas id="topMedicinesChart"></canvas>
            </div>
        </div>

        {{-- Restocking Status Chart --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Restocking Status</h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="restockingChart"></canvas>
            </div>
        </div>

        {{-- Expiration Status Chart --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Expiration Status Distribution</h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="expirationChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Recent Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            @if($recentTransactions->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-gray-500">No transactions recorded yet.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Medicine</th>
                            <th class="px-6 py-3">Qty</th>
                            <th class="px-6 py-3">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentTransactions as $tx)
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
                                <td class="px-6 py-3 font-medium {{ $tx->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $tx->direction === 'in' ? '+' : '-' }}{{ $tx->quantity }}
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ $tx->user?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Medicines Requiring Restocking --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Restocking Required</h3>
            </div>
            @if($restockAlerts->isEmpty())
                <div class="px-6 py-8 text-center text-sm text-gray-500">No active restocking alerts.</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($restockAlerts as $alert)
                        <li class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $alert->medicine?->generic_name }}</p>
                                <p class="text-xs text-gray-500">Stock: {{ $alert->current_stock_at_trigger }} / ROP: {{ $alert->rop_at_trigger }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $alert->isActive() ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Near-Expiry Medicines --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Near-Expiry Batches</h3>
            </div>
            @if($nearExpiryMeds->isEmpty())
                <div class="px-6 py-8 text-center text-sm text-gray-500">No near-expiry batches.</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($nearExpiryMeds as $batch)
                        @php $status = $batch->getExpirationStatus(); @endphp
                        <li class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $batch->medicine?->generic_name }}</p>
                                <p class="text-xs text-gray-500">Batch: {{ $batch->batch_number }} · Qty: {{ $batch->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $status === 'critical' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($status) }}
                                </span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $batch->expiration_date->format('M d, Y') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Expired Medicines --}}
    @if($expiredMeds->isNotEmpty())
    <div class="bg-white rounded-xl border border-red-200">
        <div class="px-6 py-4 border-b border-red-200 bg-red-50 rounded-t-xl">
            <h3 class="text-base font-semibold text-red-800">Expired Batches (With Remaining Stock)</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @foreach($expiredMeds as $batch)
                <li class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $batch->medicine?->generic_name }}</p>
                        <p class="text-xs text-gray-500">Batch: {{ $batch->batch_number }} · Expired: {{ $batch->expiration_date->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        {{ $batch->quantity }} remaining
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);

        // Stock Movement — Bar chart
        new Chart(document.getElementById('stockMovementChart'), {
            type: 'bar',
            data: {
                labels: chartData.stockMovement.labels,
                datasets: [
                    {
                        label: 'Stock In',
                        data: chartData.stockMovement.stockIn,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Stock Out',
                        data: chartData.stockMovement.stockOut,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 12 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                },
            },
        });

        // Top Medicines — Horizontal bar
        new Chart(document.getElementById('topMedicinesChart'), {
            type: 'bar',
            data: {
                labels: chartData.topMedicines.labels,
                datasets: [{
                    label: 'Units Issued',
                    data: chartData.topMedicines.values,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { font: { size: 11 } } },
                    y: { ticks: { font: { size: 11 } } },
                },
            },
        });

        // Restocking Status — Doughnut
        new Chart(document.getElementById('restockingChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.restockingStatus.labels,
                datasets: [{
                    data: chartData.restockingStatus.values,
                    backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 12 } } },
                },
            },
        });

        // Expiration Status — Doughnut
        new Chart(document.getElementById('expirationChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.expirationStatus.labels,
                datasets: [{
                    data: chartData.expirationStatus.values,
                    backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(107, 114, 128, 0.8)'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 12 } } },
                },
            },
        });
    });
</script>
@endpush
