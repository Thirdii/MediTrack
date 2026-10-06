<div class="max-w-3xl space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
        @if($notifications->total() > 0)
            <button wire:click="markAllAsRead" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                Mark all as read
            </button>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-12 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <p class="mt-4 text-sm text-gray-500">No notifications yet.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
            @foreach($notifications as $notification)
                <div class="px-6 py-4 {{ $notification->read_at ? 'bg-white' : 'bg-emerald-50/50' }} flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm text-gray-900 {{ $notification->read_at ? '' : 'font-medium' }}">
                            {{ $notification->data['message'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(! $notification->read_at)
                        <button wire:click="markAsRead('{{ $notification->id }}')" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium whitespace-nowrap shrink-0">
                            Mark read
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
