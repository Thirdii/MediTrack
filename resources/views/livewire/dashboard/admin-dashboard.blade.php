<div class="space-y-6">
    {{-- Admin KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Pharmacies</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalPharmacies }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Accounts</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalStaff }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Medicines</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalActiveMedicines) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Alerts</p>
            <p class="mt-2 text-2xl font-bold {{ $totalActiveAlerts > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $totalActiveAlerts }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Near-Expiry Batches</p>
            <p class="mt-2 text-2xl font-bold {{ $nearExpiryTotal > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $nearExpiryTotal }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Expired Batches</p>
            <p class="mt-2 text-2xl font-bold {{ $expiredTotal > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $expiredTotal }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Combined Inventory Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">₱{{ number_format($combinedInventoryValue, 2) }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Stock In vs Stock Out — All Pharmacies (Last 14 Days)</h3>
            <div class="relative h-64">
                <canvas id="adminStockMovementChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Restocking Status — All Pharmacies</h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="adminRestockingChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Pharmacy-by-Pharmacy Summary --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Pharmacy Summary</h3>
        </div>
        <div class="overflow-x-auto">
            @if($pharmacies->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-gray-500">No active pharmacies.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Pharmacy</th>
                            <th class="px-6 py-3">Active Medicines</th>
                            <th class="px-6 py-3">Active Alerts</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pharmacies as $pharmacy)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <p class="font-medium text-gray-900">{{ $pharmacy->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $pharmacy->address }}</p>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $pharmacy->active_medicines_count }}</td>
                                <td class="px-6 py-3">
                                    @if($pharmacy->active_alerts_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                            {{ $pharmacy->active_alerts_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('pharmacies.show', $pharmacy) }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);

        new Chart(document.getElementById('adminStockMovementChart'), {
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

        new Chart(document.getElementById('adminRestockingChart'), {
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
    });
</script>
@endpush
