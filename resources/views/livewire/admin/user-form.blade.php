<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" wire:navigate>
            ← Back to Users
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ $editing ? 'Edit User' : 'Create User' }}</h2>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input wire:model="name" type="text" id="name"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-red-400 ring-1 ring-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                <input wire:model="email" type="email" id="email"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-red-400 ring-1 ring-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                <select wire:model.live="role" id="role"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('role') border-red-400 ring-1 ring-red-400 @enderror">
                    <option value="staff">Pharmacy Staff</option>
                    <option value="admin">Global Admin</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if($role === 'staff')
                <div>
                    <label for="pharmacy_id" class="block text-sm font-medium text-gray-700 mb-1">Assigned Pharmacy *</label>
                    <select wire:model="pharmacy_id" id="pharmacy_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('pharmacy_id') border-red-400 ring-1 ring-red-400 @enderror">
                        <option value="">— Select Pharmacy —</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                    @error('pharmacy_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="border-t border-gray-200 pt-5 mt-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">
                    {{ $editing ? 'Change Password (leave blank to keep current)' : 'Password *' }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input wire:model="password" type="password" id="password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('password') border-red-400 ring-1 ring-red-400 @enderror"
                            placeholder="Minimum 8 characters">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" id="password_confirmation"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium" wire:navigate>Cancel</a>
                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-6 rounded-lg transition-colors">
                    {{ $editing ? 'Update' : 'Create' }} User
                </button>
            </div>
        </form>
    </div>
</div>
