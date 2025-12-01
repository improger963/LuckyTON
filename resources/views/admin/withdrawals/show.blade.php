@extends('admin.layouts.modern-app')

@section('title', 'Withdrawal Details')
@section('subtitle', 'View withdrawal request information')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.withdrawals.index') }}" icon="bi bi-arrow-left">
    Back to Withdrawals
</x-admin.button>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Withdrawal Info -->
    <div class="lg:col-span-2">
        <x-admin.card title="Withdrawal Request #{{ $withdrawal->id }}" subtitle="Detailed information about this withdrawal request">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Amount
                    </label>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        ${{ number_format(abs($withdrawal->amount), 2) }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status
                    </label>
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                Cancelled
                            </span>
                            @break
                        @case('failed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Failed
                            </span>
                            @break
                    @endswitch
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Requested Date
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $withdrawal->created_at->format('M d, Y H:i:s') }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Last Updated
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $withdrawal->updated_at->format('M d, Y H:i:s') }}
                    </p>
                </div>
            </div>
            
            <!-- Metadata -->
            @if($withdrawal->metadata)
            <div class="mt-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Withdrawal Details</h3>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    @foreach($withdrawal->metadata as $key => $value)
                    <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            @if(is_array($value))
                                {{ json_encode($value) }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </x-admin.card>
    </div>
    
    <!-- User Info -->
    <div class="lg:col-span-1">
        <x-admin.card title="User Information" subtitle="Details about the user who requested this withdrawal">
            @if($withdrawal->wallet && $withdrawal->wallet->user)
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center mr-3 text-white font-bold">
                    {{ substr($withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email, 0, 1) }}
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        {{ $withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        User ID: {{ $withdrawal->wallet->user->id }}
                    </p>
                </div>
            </div>
            
            <div class="mt-4">
                <x-admin.button variant="secondary" href="{{ route('admin.users.show', $withdrawal->wallet->user) }}" icon="bi bi-eye" class="w-full">
                    View User Profile
                </x-admin.button>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Current Balance
                </label>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                    ${{ number_format($withdrawal->wallet->balance, 2) }}
                </p>
            </div>
            @else
            <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                User information not available
            </p>
            @endif
        </x-admin.card>
        
        <!-- Actions -->
        @if($withdrawal->status === 'pending')
        <x-admin.card title="Actions" subtitle="Manage this withdrawal request" class="mt-6">
            <div class="space-y-3">
                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                    @csrf
                    <x-admin.button variant="success" icon="bi bi-check-circle" type="submit" class="w-full"
                        onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                        Approve Withdrawal
                    </x-admin.button>
                </form>
                
                <button type="button" 
                    class="w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm"
                    onclick="document.getElementById('reject-modal-{{ $withdrawal->id }}').classList.remove('hidden')">
                    <i class="bi bi-x-circle mr-1"></i> Reject Withdrawal
                </button>
                
                <form method="POST" action="{{ route('admin.withdrawals.cancel', $withdrawal) }}">
                    @csrf
                    <x-admin.button variant="secondary" icon="bi bi-slash-circle" type="submit" class="w-full"
                        onclick="return confirm('Are you sure you want to cancel this withdrawal?')">
                        Cancel Withdrawal
                    </x-admin.button>
                </form>
            </div>
        </x-admin.card>
        @endif
    </div>
</div>

<!-- Rejection Modal -->
@if($withdrawal->status === 'pending')
<x-admin.modal id="reject-modal-{{ $withdrawal->id }}" title="Reject Withdrawal" size="md">
    <x-slot name="icon">
        <i class="bi bi-x-circle text-red-600 text-xl"></i>
    </x-slot>
    
    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
        @csrf
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Are you sure you want to reject this withdrawal request? Please provide a reason.
        </p>
        
        <div class="mt-4">
            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Reason for rejection
            </label>
            <textarea id="reason" name="reason" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                      required></textarea>
        </div>
        
        <x-slot name="footer">
            <x-admin.button variant="danger" type="submit">
                Reject
            </x-admin.button>
            <x-admin.button variant="secondary" onclick="document.getElementById('reject-modal-{{ $withdrawal->id }}').classList.add('hidden')" class="ml-2">
                Cancel
            </x-admin.button>
        </x-slot>
    </form>
</x-admin.modal>
@endif
@endsection