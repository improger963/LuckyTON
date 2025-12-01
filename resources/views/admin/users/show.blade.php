@extends('admin.layouts.modern-app')

@section('title', 'User Details')
@section('subtitle', 'View user information')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.users.index') }}" icon="bi bi-arrow-left">
    Back to Users
</x-admin.button>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <x-admin.card>
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center mb-4 text-white text-3xl font-bold">
                    {{ substr($user->username ?? $user->email, 0, 1) }}
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $user->username ?? $user->email }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Member since {{ $user->created_at->format('M d, Y') }}
                </p>
                
                <div class="mt-6 w-full space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status:</span>
                        @if($user->banned_at)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Banned
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Active
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Premium:</span>
                        @if($user->is_premium)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Yes
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                No
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">PIN Enabled:</span>
                        @if($user->is_pin_enabled)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Yes
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                No
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-2">
                    <x-admin.button variant="secondary" href="{{ route('admin.users.edit', $user) }}" icon="bi bi-pencil">
                        Edit
                    </x-admin.button>
                    
                    @if($user->banned_at)
                        <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                            @csrf
                            <x-admin.button variant="success" icon="bi bi-unlock" type="submit">
                                Unban
                            </x-admin.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                            @csrf
                            <x-admin.button variant="danger" icon="bi bi-lock" type="submit"
                                onclick="return confirm('Are you sure you want to ban this user?')">
                                Ban
                            </x-admin.button>
                        </form>
                    @endif
                </div>
            </div>
        </x-admin.card>
        
        <!-- Balance Card -->
        <x-admin.card title="Wallet" class="mt-6">
            @if($user->wallet)
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($user->wallet->balance, 2) }}
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">
                        Wallet Address: {{ $user->wallet->address }}
                    </p>
                    <div class="mt-4">
                        <x-admin.button variant="primary" href="{{ route('admin.users.edit', $user) }}#balance" icon="bi bi-currency-dollar">
                            Adjust Balance
                        </x-admin.button>
                    </div>
                </div>
            @else
                <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No wallet found
                </p>
            @endif
        </x-admin.card>
    </div>
    
    <!-- User Details -->
    <div class="lg:col-span-2">
        <x-admin.card title="User Information" subtitle="Detailed user profile information">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Username
                    </label>
                    <p class="text-gray-900 dark:text-white">{{ $user->username ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <p class="text-gray-900 dark:text-white">{{ $user->email }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Referral Code
                    </label>
                    <p class="text-gray-900 dark:text-white">{{ $user->referral_code ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Referred By
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        @if($user->referrer)
                            {{ $user->referrer->username ?? $user->referrer->email }}
                        @else
                            None
                        @endif
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Total Referrals
                    </label>
                    <p class="text-gray-900 dark:text-white">{{ $user->referrals()->count() }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Total Earnings
                    </label>
                    <p class="text-gray-900 dark:text-white">${{ number_format($user->total_referral_earnings, 2) }}</p>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Last Updated
                </label>
                <p class="text-gray-900 dark:text-white">
                    {{ $user->updated_at->format('M d, Y H:i:s') }}
                </p>
            </div>
        </x-admin.card>
        
        <!-- Referrals -->
        <x-admin.card title="Referrals" subtitle="Users referred by this user" class="mt-6">
            @if($user->referrals()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Username</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Earnings</th>
                                <th scope="col" class="px-6 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->referrals as $referral)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="px-6 py-4">{{ $referral->username ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $referral->email }}</td>
                                <td class="px-6 py-4">
                                    ${{ number_format($referral->total_referral_earnings, 2) }}
                                </td>
                                <td class="px-6 py-4">{{ $referral->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No referrals found
                </p>
            @endif
        </x-admin.card>
    </div>
</div>
@endsection