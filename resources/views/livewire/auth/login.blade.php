<div class="w-full max-w-md">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Welcome back</h2>
        <p class="text-sm text-gray-500 text-center mb-8">Sign in to your MediTrack account</p>

        <form wire:submit="login" class="space-y-5">
            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input wire:model="email" type="email" id="email" autocomplete="email" autofocus
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('email') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input wire:model="password" type="password" id="password" autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('password') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="remember" type="checkbox"
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" wire:navigate>
                    Forgot password?
                </a>
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Sign in</span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Signing in…
                </span>
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        MediTrack — Pharmacy Inventory Management System
    </p>
</div>
