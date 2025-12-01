@extends('admin.layouts.modern-app')

@section('title', 'Edit User')
@section('subtitle', 'Modify user information')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.users.index') }}" icon="bi bi-arrow-left">
    Back to Users
</x-admin.button>
@endsection

@section('content')
<x-admin.card title="Edit User" subtitle="Update user information">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-admin.form-input 
                label="Username" 
                name="username" 
                value="{{ old('username', $user->username) }}" 
                placeholder="Enter username" 
                error="true" />
            
            <x-admin.form-input 
                label="Email" 
                name="email" 
                type="email" 
                value="{{ old('email', $user->email) }}" 
                placeholder="Enter email" 
                error="true" />
            
            <div>
                <label for="is_premium" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Premium Status
                </label>
                <select name="is_premium" id="is_premium" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                    <option value="0" {{ old('is_premium', $user->is_premium) == 0 ? 'selected' : '' }}>
                        Regular User
                    </option>
                    <option value="1" {{ old('is_premium', $user->is_premium) == 1 ? 'selected' : '' }}>
                        Premium User
                    </option>
                </select>
                @error('is_premium')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="is_pin_enabled" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    PIN Enabled
                </label>
                <select name="is_pin_enabled" id="is_pin_enabled" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                    <option value="0" {{ old('is_pin_enabled', $user->is_pin_enabled) == 0 ? 'selected' : '' }}>
                        Disabled
                    </option>
                    <option value="1" {{ old('is_pin_enabled', $user->is_pin_enabled) == 1 ? 'selected' : '' }}>
                        Enabled
                    </option>
                </select>
                @error('is_pin_enabled')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex items-center">
            <x-admin.button variant="primary" icon="bi bi-save" type="submit">
                Save Changes
            </x-admin.button>
            <x-admin.button variant="secondary" href="{{ route('admin.users.index') }}" class="ml-2">
                Cancel
            </x-admin.button>
        </div>
    </form>
</x-admin.card>

<!-- Ban/Unban Form -->
<x-admin.card title="Account Status" subtitle="Manage user account status" class="mt-6">
    @if($user->banned_at)
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-medium text-gray-900 dark:text-white">User is Banned</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                    Banned on {{ $user->banned_at->format('M d, Y H:i:s') }}
                </p>
            </div>
            <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-unlock" type="submit">
                    Unban User
                </x-admin.button>
            </form>
        </div>
    @else
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-medium text-gray-900 dark:text-white">User is Active</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                    Account is currently active
                </p>
            </div>
            <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                @csrf
                <x-admin.button variant="danger" icon="bi bi-lock" type="submit" 
                    onclick="return confirm('Are you sure you want to ban this user?')">
                    Ban User
                </x-admin.button>
            </form>
        </div>
    @endif
</x-admin.card>

<!-- Balance Adjustment -->
<x-admin.card title="Adjust Balance" subtitle="Add or subtract funds from user's account" class="mt-6">
    <form method="POST" action="{{ route('admin.users.balance.adjust', $user) }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Amount
                </label>
                <input type="number" name="amount" id="amount" step="0.01"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" 
                       placeholder="0.00">
                @error('amount')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Type
                </label>
                <select name="type" id="type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                    <option value="deposit">Add Funds</option>
                    <option value="withdrawal">Subtract Funds</option>
                </select>
            </div>
            
            <x-admin.form-input 
                label="Reason" 
                name="reason" 
                placeholder="Reason for adjustment" 
                error="true" />
        </div>
        
        <div class="mt-4">
            <x-admin.button variant="primary" icon="bi bi-currency-dollar" type="submit">
                Adjust Balance
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection