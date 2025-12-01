@extends('admin.layouts.modern-app')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome to your admin dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Users Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <i class="bi bi-people text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_users'] ?? 0 }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center text-sm">
                <i class="bi bi-arrow-up text-green-500 mr-1"></i>
                <span class="text-green-500 font-medium">+{{ $stats['recent_registrations'] ?? 0 }} today</span>
                <span class="text-gray-500 dark:text-gray-400 ml-2">new registrations</span>
            </div>
        </div>
    </div>

    <!-- Total Transactions Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                <i class="bi bi-graph-up text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_transactions'] ?? 0 }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Transactions</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center text-sm">
                <i class="bi bi-arrow-down text-red-500 mr-1"></i>
                <span class="text-red-500 font-medium">-2.5%</span>
                <span class="text-gray-500 dark:text-gray-400 ml-2">from last week</span>
            </div>
        </div>
    </div>

    <!-- Pending Withdrawals Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                <i class="bi bi-cash-coin text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending_withdrawals'] ?? 0 }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending Withdrawals</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center text-sm">
                @if(($stats['pending_withdrawals'] ?? 0) > 0)
                    <i class="bi bi-exclamation-triangle text-orange-500 mr-1"></i>
                    <span class="text-orange-500 font-medium">Attention needed</span>
                @else
                    <i class="bi bi-check-circle text-green-500 mr-1"></i>
                    <span class="text-green-500 font-medium">All clear</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Game Rooms Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                <i class="bi bi-controller text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_rooms'] ?? 0 }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Active Rooms</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center text-sm">
                <i class="bi bi-arrow-up text-green-500 mr-1"></i>
                <span class="text-green-500 font-medium">+5%</span>
                <span class="text-gray-500 dark:text-gray-400 ml-2">from yesterday</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Tables Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Registration Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">User Registrations</h2>
            <div class="flex space-x-2">
                <button class="text-xs px-3 py-1 rounded-lg bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">7 Days</button>
                <button class="text-xs px-3 py-1 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">30 Days</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="registrationChart"></canvas>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Overview</h2>
            <div class="flex space-x-2">
                <button class="text-xs px-3 py-1 rounded-lg bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">7 Days</button>
                <button class="text-xs px-3 py-1 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">30 Days</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    View all
                </a>
            </div>
        </div>
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
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                    {{ substr($user->username ?? $user->email, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $user->username ?? $user->email }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
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
    </div>

    <!-- Recent Activities -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activities</h2>
        </div>
        <div class="p-6">
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
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Registration Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        const registrationChart = new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Registrations',
                    data: {!! json_encode($chartData['data'] ?? []) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Revenue',
                    data: [1200, 1900, 1500, 2200, 1800, 2500, 2100],
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection