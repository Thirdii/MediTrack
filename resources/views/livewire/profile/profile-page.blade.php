<div class="max-w-2xl space-y-6">
    {{-- Profile Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Profile Information</h2>
        <p class="text-sm text-gray-500 mb-6">Update your account's display name.</p>

        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input wire:model="name" type="text" id="name"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-red-400 ring-1 ring-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input type="email" value="{{ $email }}" disabled
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Email cannot be changed. Contact an administrator if needed.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
                @if(auth()->user()->isStaff() && auth()->user()->pharmacy)
                    <span class="ml-2 text-sm text-gray-500">— {{ auth()->user()->pharmacy->name }}</span>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                    Save
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Change Password</h2>
        <p class="text-sm text-gray-500 mb-6">Ensure your account uses a strong password.</p>

        <form wire:submit="updatePassword" class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                <input wire:model="current_password" type="password" id="current_password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('current_password') border-red-400 ring-1 ring-red-400 @enderror">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                <input wire:model="new_password" type="password" id="new_password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('new_password') border-red-400 ring-1 ring-red-400 @enderror"
                    placeholder="Minimum 8 characters">
                @error('new_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                <input wire:model="new_password_confirmation" type="password" id="new_password_confirmation"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                    Change password
                </button>
            </div>
        </form>
    </div>
</div>
