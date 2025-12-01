@extends('admin.layouts.modern-app')

@section('title', 'Create User')
@section('subtitle', 'Add a new user account')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.users.index') }}" icon="bi bi-arrow-left">
    Back to Users
</x-admin.button>
@endsection

@section('content')
<x-admin.card title="Create New User" subtitle="Fill in the form below to add a new user">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-admin.form-input 
                label="Username" 
                name="username" 
                value="{{ old('username') }}" 
                placeholder="Enter username" 
                error="true" />
            
            <x-admin.form-input 
                label="Email" 
                name="email" 
                type="email" 
                value="{{ old('email') }}" 
                placeholder="Enter email" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Password" 
                name="password" 
                type="password" 
                placeholder="Enter password" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Confirm Password" 
                name="password_confirmation" 
                type="password" 
                placeholder="Confirm password" 
                required="true" 
                error="true" />
            
            <div>
                <label for="is_premium" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Premium Status
                </label>
                <select name="is_premium" id="is_premium" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                    <option value="0" {{ old('is_premium') == 0 ? 'selected' : '' }}>
                        Regular User
                    </option>
                    <option value="1" {{ old('is_premium') == 1 ? 'selected' : '' }}>
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
                    <option value="0" {{ old('is_pin_enabled') == 0 ? 'selected' : '' }}>
                        Disabled
                    </option>
                    <option value="1" {{ old('is_pin_enabled') == 1 ? 'selected' : '' }}>
                        Enabled
                    </option>
                </select>
                @error('is_pin_enabled')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex items-center">
            <x-admin.button variant="primary" icon="bi bi-plus-circle" type="submit">
                Create User
            </x-admin.button>
            <x-admin.button variant="secondary" href="{{ route('admin.users.index') }}" class="ml-2">
                Cancel
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection