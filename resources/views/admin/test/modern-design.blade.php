@extends('admin.layouts.modern-app')

@section('title', 'Modern Design Test')
@section('subtitle', 'Testing the new admin panel design')

@section('actions')
    <x-admin.button variant="primary" icon="bi bi-plus-lg">
        Add Item
    </x-admin.button>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-admin.stat-card 
            icon="bi bi-people" 
            iconBgColor="bg-blue-100" 
            iconColor="text-blue-600" 
            value="1,250" 
            label="Total Users" 
            trend="+12%" 
            trendDirection="up" 
            trendLabel="from last month" />
        
        <x-admin.stat-card 
            icon="bi bi-currency-dollar" 
            iconBgColor="bg-green-100" 
            iconColor="text-green-600" 
            value="$24,560" 
            label="Revenue" 
            trend="+8.2%" 
            trendDirection="up" 
            trendLabel="from last month" />
        
        <x-admin.stat-card 
            icon="bi bi-cart" 
            iconBgColor="bg-purple-100" 
            iconColor="text-purple-600" 
            value="1,892" 
            label="Orders" 
            trend="-2.5%" 
            trendDirection="down" 
            trendLabel="from last month" />
        
        <x-admin.stat-card 
            icon="bi bi-chat" 
            iconBgColor="bg-yellow-100" 
            iconColor="text-yellow-600" 
            value="89" 
            label="Messages" 
            trend="+5.3%" 
            trendDirection="up" 
            trendLabel="from last hour" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card title="Recent Activity" subtitle="Latest user activities">
            <div class="space-y-4">
                <div class="flex items-start p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            John Doe registered
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            2 minutes ago
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 flex items-center justify-center">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            $250.00 deposited
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            15 minutes ago
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 flex items-center justify-center">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            Tournament won
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            1 hour ago
                        </p>
                    </div>
                </div>
            </div>
        </x-admin.card>
        
        <x-admin.card title="Quick Actions" subtitle="Common administrative tasks">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.button variant="primary" class="w-full justify-center">
                    <i class="bi bi-people mr-2"></i> Manage Users
                </x-admin.button>
                
                <x-admin.button variant="success" class="w-full justify-center">
                    <i class="bi bi-currency-dollar mr-2"></i> View Payments
                </x-admin.button>
                
                <x-admin.button variant="warning" class="w-full justify-center">
                    <i class="bi bi-controller mr-2"></i> Game Rooms
                </x-admin.button>
                
                <x-admin.button variant="danger" class="w-full justify-center">
                    <i class="bi bi-shield-lock mr-2"></i> Security
                </x-admin.button>
            </div>
        </x-admin.card>
    </div>

    <x-admin.data-table header="User Management">
        <x-slot name="actions">
            <div class="flex space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search users..." class="w-full md:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <x-admin.button variant="secondary">
                    <i class="bi bi-funnel"></i>
                </x-admin.button>
            </div>
        </x-slot>
        
        <x-slot name="thead">
            <th scope="col" class="px-6 py-3">User</th>
            <th scope="col" class="px-6 py-3">Email</th>
            <th scope="col" class="px-6 py-3">Status</th>
            <th scope="col" class="px-6 py-3">Actions</th>
        </x-slot>
        
        <x-slot name="tbody">
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                            J
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900 dark:text-white">
                                John Doe
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                ID: 12345
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    john.doe@example.com
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Active
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex space-x-2">
                        <x-admin.button variant="secondary" size="sm">
                            <i class="bi bi-eye"></i>
                        </x-admin.button>
                        <x-admin.button variant="secondary" size="sm">
                            <i class="bi bi-pencil"></i>
                        </x-admin.button>
                        <x-admin.button variant="danger" size="sm">
                            <i class="bi bi-trash"></i>
                        </x-admin.button>
                    </div>
                </td>
            </tr>
            
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold">
                            S
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900 dark:text-white">
                                Sarah Smith
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                ID: 12346
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    sarah.smith@example.com
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Banned
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex space-x-2">
                        <x-admin.button variant="secondary" size="sm">
                            <i class="bi bi-eye"></i>
                        </x-admin.button>
                        <x-admin.button variant="secondary" size="sm">
                            <i class="bi bi-pencil"></i>
                        </x-admin.button>
                        <x-admin.button variant="success" size="sm">
                            <i class="bi bi-unlock"></i>
                        </x-admin.button>
                    </div>
                </td>
            </tr>
        </x-slot>
        
        <x-slot name="footer">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">2</span> of <span class="font-medium">2</span> results
                </div>
                <div class="flex space-x-2">
                    <x-admin.button variant="secondary" size="sm">
                        Previous
                    </x-admin.button>
                    <x-admin.button variant="secondary" size="sm">
                        Next
                    </x-admin.button>
                </div>
            </div>
        </x-slot>
    </x-admin.data-table>
@endsection