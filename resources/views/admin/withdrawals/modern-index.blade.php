@extends('admin.layouts.modern-app')

@section('title', 'Withdrawals')
@section('subtitle', 'Manage withdrawal requests')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Withdrawal Requests</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="flex space-x-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search withdrawals..." 
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
                    <th scope="col" class="px-6 py-3">Amount</th>
                    <th scope="col" class="px-6 py-3">Description</th>
                    <th scope="col" class="px-6 py-3">Requested</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $withdrawal)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                {{ substr($withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    ID: {{ $withdrawal->wallet->user->id }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-red-600 dark:text-red-400">
                            -${{ number_format(abs($withdrawal->amount), 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-xs truncate">
                            {{ $withdrawal->description ?? 'No description' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $withdrawal->created_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4">
                        @switch($withdrawal->status)
                            @case('pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 animate-pulse">
                                    Pending
                                </span>
                                @break
                            @case('completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Completed
                                </span>
                                @break
                            @case('cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Cancelled
                                </span>
                                @break
                        @endswitch
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @if($withdrawal->status === 'pending')
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to approve this withdrawal?')"
                                            title="Approve">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                
                                <button type="button" 
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors duration-200"
                                        onclick="document.getElementById('reject-modal-{{ $withdrawal->id }}').classList.remove('hidden')"
                                        title="Reject">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-cash-coin text-3xl mb-3 block"></i>
                        <p>No withdrawal requests found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($withdrawals->hasPages())
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
                Showing <span class="font-medium">{{ $withdrawals->firstItem() }}</span> to <span class="font-medium">{{ $withdrawals->lastItem() }}</span> of <span class="font-medium">{{ $withdrawals->total() }}</span> results
            </div>
            <div>
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Rejection Modals -->
@foreach($withdrawals as $withdrawal)
@if($withdrawal->status === 'pending')
<div id="reject-modal-{{ $withdrawal->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="bi bi-x-circle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Reject Withdrawal
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to reject this withdrawal request? Please provide a reason.
                                </p>
                                <div class="mt-4">
                                    <label for="reason-{{ $withdrawal->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Reason for rejection
                                    </label>
                                    <textarea id="reason-{{ $withdrawal->id }}" name="reason" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                              required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('reject-modal-{{ $withdrawal->id }}').classList.add('hidden')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection