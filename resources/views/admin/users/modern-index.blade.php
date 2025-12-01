@extends('admin.layouts.modern-app')

@section('title', 'Users')
@section('subtitle', 'Manage user accounts')

@section('actions')
<a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
    <i class="bi bi-plus-lg mr-2"></i> Add User
</a>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Users Management</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex space-x-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search users..." 
                               class="w-full md:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded-lg transition-colors duration-200">
                        <i class="bi bi-funnel"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">User</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Balance</th>
                    <th scope="col" class="px-6 py-3">Referrals</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                {{ substr($user->username ?? $user->email, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $user->username ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    ID: {{ $user->id }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4">
                        @if($user->wallet)
                            <span class="font-medium text-green-600 dark:text-green-400">
                                ${{ number_format($user->wallet->balance, 2) }}
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">
                                $0.00
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium">
                            {{ $user->referrals()->count() }}
                        </span>
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
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->banned_at)
                                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to unban this user?')"
                                            title="Unban">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to ban this user?')"
                                            title="Ban">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-person-x text-3xl mb-3 block"></i>
                        <p>No users found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
                Showing <span class="font-medium">{{ $users->firstItem() }}</span> to <span class="font-medium">{{ $users->lastItem() }}</span> of <span class="font-medium">{{ $users->total() }}</span> results
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection