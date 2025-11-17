@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('subtitle', 'Modify user information')

@section('actions')
<a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Users
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="admin-form-label">Username</label>
                    <input type="text" name="username" id="username" 
                           value="{{ old('username', $user->username) }}"
                           class="admin-form-input">
                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="admin-form-label">Email</label>
                    <input type="email" name="email" id="email" 
                           value="{{ old('email', $user->email) }}"
                           class="admin-form-input">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="is_premium" class="admin-form-label">Premium Status</label>
                    <select name="is_premium" id="is_premium" class="admin-form-input">
                        <option value="0" {{ old('is_premium', $user->is_premium) == 0 ? 'selected' : '' }}>
                            Regular User
                        </option>
                        <option value="1" {{ old('is_premium', $user->is_premium) == 1 ? 'selected' : '' }}>
                            Premium User
                        </option>
                    </select>
                    @error('is_premium')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="is_pin_enabled" class="admin-form-label">PIN Enabled</label>
                    <select name="is_pin_enabled" id="is_pin_enabled" class="admin-form-input">
                        <option value="0" {{ old('is_pin_enabled', $user->is_pin_enabled) == 0 ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="1" {{ old('is_pin_enabled', $user->is_pin_enabled) == 1 ? 'selected' : '' }}>
                            Enabled
                        </option>
                    </select>
                    @error('is_pin_enabled')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="admin-btn-primary">
                    <i class="bi bi-save mr-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ban/Unban Form -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Account Status</h2>
    </div>
    
    <div class="admin-card-body">
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
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-unlock mr-1"></i> Unban User
                    </button>
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
                    <button type="submit" class="admin-btn-danger"
                            onclick="return confirm('Are you sure you want to ban this user?')">
                        <i class="bi bi-lock mr-1"></i> Ban User
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<!-- Balance Adjustment -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Adjust Balance</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.users.balance.adjust', $user) }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="amount" class="admin-form-label">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.01"
                           class="admin-form-input" placeholder="0.00">
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="type" class="admin-form-label">Type</label>
                    <select name="type" id="type" class="admin-form-input">
                        <option value="add">Add Funds</option>
                        <option value="subtract">Subtract Funds</option>
                    </select>
                </div>
                
                <div>
                    <label for="reason" class="admin-form-label">Reason</label>
                    <input type="text" name="reason" id="reason" 
                           class="admin-form-input" placeholder="Reason for adjustment">
                    @error('reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="admin-btn-primary">
                    <i class="bi bi-currency-dollar mr-1"></i> Adjust Balance
                </button>
            </div>
        </form>
    </div>
</div>
@endsection