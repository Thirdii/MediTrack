<div class="w-full max-w-md">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Reset password</h2>
        <p class="text-sm text-gray-500 text-center mb-8">Enter your new password below.</p>

        <form wire:submit="resetPassword" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input wire:model="email" type="email" id="email" autocomplete="email"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-gray-50 @error('email') border-red-400 ring-1 ring-red-400 @enderror"
                    readonly>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                <input wire:model="password" type="password" id="password" autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('password') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="Minimum 8 characters">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                <input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>

            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Reset password</span>
                <span wire:loading>Resetting…</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" wire:navigate>
                ← Back to login
            </a>
        </div>
    </div>
</div>
