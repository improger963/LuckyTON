@extends('admin.layouts.modern-app')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome to your admin dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Users Card -->
    <x-admin.stat-card 
        icon="bi bi-people" 
        iconBgColor="bg-blue-100" 
        iconColor="text-blue-600" 
        value="{{ $stats['total_users'] ?? 0 }}" 
        label="Total Users" 
        trend="+{{ $stats['new_users_today'] ?? 0 }} today" 
        trendDirection="up" />

    <!-- Total Game Rooms Card -->
    <x-admin.stat-card 
        icon="bi bi-controller" 
        iconBgColor="bg-purple-100" 
        iconColor="text-purple-600" 
        value="{{ $stats['total_rooms'] ?? 0 }}" 
        label="Game Rooms" 
        trend="Active" 
        trendDirection="up" />

    <!-- Pending Withdrawals Card -->
    <x-admin.stat-card 
        icon="bi bi-cash-coin" 
        iconBgColor="bg-orange-100" 
        iconColor="text-orange-600" 
        value="{{ $stats['pending_withdrawals'] ?? 0 }}" 
        label="Pending Withdrawals" 
        trend="{{ ($stats['pending_withdrawals'] ?? 0) > 0 ? 'Attention needed' : 'All clear' }}" 
        trendDirection="{{ ($stats['pending_withdrawals'] ?? 0) > 0 ? 'down' : 'up' }}" />

    <!-- Total Transactions Card -->
    <x-admin.stat-card 
        icon="bi bi-graph-up" 
        iconBgColor="bg-green-100" 
        iconColor="text-green-600" 
        value="{{ $stats['total_transactions'] ?? 0 }}" 
        label="Transactions" 
        trend="-2.5%" 
        trendDirection="down" 
        trendLabel="from last week" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <x-admin.card title="Recent Users" subtitle="Latest registered users">
        <x-slot name="actions">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                View all
            </a>
        </x-slot>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">User</th>
                        <th scope="col" class="px-6 py-3">Registered</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestUsers as $user)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                    {{ substr($user->username ?? $user->email, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $user->username ?? $user->email }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($user->banned_at)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Banned
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No users found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <!-- Recent Activities -->
    <x-admin.card title="Recent Activities" subtitle="Latest transactions">
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            <div class="flex items-start p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $activity->description ?? 'Transaction' }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $activity->wallet->user->username ?? 'Unknown User' }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $activity->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-center py-4 text-gray-500 dark:text-gray-400">No recent activities</p>
            @endforelse
        </div>
    </x-admin.card>
</div>
@endsection