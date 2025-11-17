@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome to your admin dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Users Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-people text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number">{{ $stats['total_users'] ?? 0 }}</h3>
                <p class="admin-stat-label">Total Users</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>+{{ $stats['new_users_today'] ?? 0 }} today</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Game Rooms Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-controller text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number">{{ $stats['total_rooms'] ?? 0 }}</h3>
                <p class="admin-stat-label">Game Rooms</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Withdrawals Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-cash-coin text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number">{{ $stats['pending_withdrawals'] ?? 0 }}</h3>
                <p class="admin-stat-label">Pending Withdrawals</p>
                @if(($stats['pending_withdrawals'] ?? 0) > 0)
                <div class="admin-stat-trend down">
                    <i class="bi bi-exclamation-triangle mr-1"></i>
                    <span>Attention needed</span>
                </div>
                @else
                <div class="admin-stat-trend up">
                    <i class="bi bi-check-circle mr-1"></i>
                    <span>All clear</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Total Tournaments Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-trophy text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number">{{ $stats['total_tournaments'] ?? 0 }}</h3>
                <p class="admin-stat-label">Tournaments</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <div class="admin-card fade-in">
        <div class="admin-card-header">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    View all
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestUsers as $user)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="admin-user-avatar admin-user-avatar-sm">
                                        {{ substr($user->username ?? $user->email, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $user->username ?? $user->email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                @if($user->banned_at)
                                    <span class="admin-badge-danger">Banned</span>
                                @else
                                    <span class="admin-badge-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No users found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="admin-card fade-in">
        <div class="admin-card-header">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activities</h2>
        </div>
        <div class="admin-card-body">
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                <div class="admin-activity-item">
                    <div class="admin-activity-icon">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['description'] ?? 'Activity' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['user'] ?? 'Unknown User' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $activity['time'] ?? 'Just now' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center py-4 text-gray-500 dark:text-gray-400">No recent activities</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection