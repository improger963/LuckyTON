@extends('admin.layouts.app')

@section('title', 'User Details')
@section('subtitle', 'View user information')

@section('actions')
<a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Users
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <i class="bi bi-person text-3xl"></i>
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
                                <span class="admin-badge-danger">Banned</span>
                            @else
                                <span class="admin-badge-success">Active</span>
                            @endif
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Premium:</span>
                            @if($user->is_premium)
                                <span class="admin-badge-success">Yes</span>
                            @else
                                <span class="admin-badge-secondary">No</span>
                            @endif
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">PIN Enabled:</span>
                            @if($user->is_pin_enabled)
                                <span class="admin-badge-success">Yes</span>
                            @else
                                <span class="admin-badge-secondary">No</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Balance Card -->
        <div class="admin-card mt-6">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Wallet</h3>
            </div>
            <div class="admin-card-body">
                @if($user->wallet)
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($user->wallet->balance, 2) }}
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Wallet Address: {{ $user->wallet->address }}
                        </p>
                        <div class="mt-4">
                            <a href="#" class="admin-btn-primary">
                                <i class="bi bi-currency-dollar mr-1"></i> Adjust Balance
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No wallet found
                    </p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- User Details -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">User Information</h3>
            </div>
            <div class="admin-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="admin-form-label">Username</label>
                        <p class="text-gray-900 dark:text-white">{{ $user->username ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Email</label>
                        <p class="text-gray-900 dark:text-white">{{ $user->email }}</p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Referral Code</label>
                        <p class="text-gray-900 dark:text-white">{{ $user->referral_code ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Referred By</label>
                        <p class="text-gray-900 dark:text-white">
                            @if($user->referrer)
                                {{ $user->referrer->username ?? $user->referrer->email }}
                            @else
                                None
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Total Referrals</label>
                        <p class="text-gray-900 dark:text-white">{{ $user->referrals()->count() }}</p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Total Earnings</label>
                        <p class="text-gray-900 dark:text-white">${{ number_format($user->total_referral_earnings, 2) }}</p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="admin-form-label">Last Updated</label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $user->updated_at->format('M d, Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Referrals -->
        <div class="admin-card mt-6">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Referrals</h3>
            </div>
            <div class="admin-card-body">
                @if($user->referrals()->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Earnings</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->referrals as $referral)
                                <tr>
                                    <td>{{ $referral->username ?? 'N/A' }}</td>
                                    <td>{{ $referral->email }}</td>
                                    <td>
                                        ${{ number_format($referral->total_referral_earnings, 2) }}
                                    </td>
                                    <td>{{ $referral->created_at->format('M d, Y') }}</td>
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
            </div>
        </div>
    </div>
</div>
@endsection