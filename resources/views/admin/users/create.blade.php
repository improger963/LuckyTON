@extends('admin.layouts.app')

@section('title', 'Create User')
@section('subtitle', 'Add a new user account')

@section('actions')
<a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Users
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New User</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="admin-form-label">Username</label>
                    <input type="text" name="username" id="username" 
                           value="{{ old('username') }}"
                           class="admin-form-input">
                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="admin-form-label">Email</label>
                    <input type="email" name="email" id="email" 
                           value="{{ old('email') }}"
                           class="admin-form-input" required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="admin-form-label">Password</label>
                    <input type="password" name="password" id="password" 
                           class="admin-form-input" required>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="admin-form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="admin-form-input" required>
                    @error('password_confirmation')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="is_premium" class="admin-form-label">Premium Status</label>
                    <select name="is_premium" id="is_premium" class="admin-form-input">
                        <option value="0" {{ old('is_premium') == 0 ? 'selected' : '' }}>
                            Regular User
                        </option>
                        <option value="1" {{ old('is_premium') == 1 ? 'selected' : '' }}>
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
                        <option value="0" {{ old('is_pin_enabled') == 0 ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="1" {{ old('is_pin_enabled') == 1 ? 'selected' : '' }}>
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
                    <i class="bi bi-plus-circle mr-1"></i> Create User
                </button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection