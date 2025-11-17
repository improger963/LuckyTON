@extends('admin.layouts.app')

@section('title', 'Withdrawal Details')
@section('subtitle', 'View withdrawal request information')

@section('actions')
<a href="{{ route('admin.withdrawals.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Withdrawals
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Withdrawal Info -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Withdrawal Request #{{ $withdrawal->id }}</h2>
            </div>
            
            <div class="admin-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="admin-form-label">Amount</label>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($withdrawal->amount, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Status</label>
                        @switch($withdrawal->status)
                            @case('pending')
                                <span class="admin-badge-warning text-lg">Pending</span>
                                @break
                            @case('completed')
                                <span class="admin-badge-success text-lg">Completed</span>
                                @break
                            @case('cancelled')
                                <span class="admin-badge-secondary text-lg">Cancelled</span>
                                @break
                            @case('failed')
                                <span class="admin-badge-danger text-lg">Failed</span>
                                @break
                        @endswitch
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Requested Date</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $withdrawal->created_at->format('M d, Y H:i:s') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Last Updated</label>
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
            </div>
        </div>
    </div>
    
    <!-- User Info -->
    <div class="lg:col-span-1">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">User Information</h3>
            </div>
            
            <div class="admin-card-body">
                @if($withdrawal->wallet && $withdrawal->wallet->user)
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                        <i class="bi bi-person"></i>
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
                    <a href="{{ route('admin.users.show', $withdrawal->wallet->user) }}" 
                       class="admin-btn-secondary w-full text-center">
                        <i class="bi bi-eye mr-1"></i> View User Profile
                    </a>
                </div>
                
                <div class="mt-4">
                    <label class="admin-form-label">Current Balance</label>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ${{ number_format($withdrawal->wallet->balance, 2) }}
                    </p>
                </div>
                @else
                <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                    User information not available
                </p>
                @endif
            </div>
        </div>
        
        <!-- Actions -->
        @if($withdrawal->status === 'pending')
        <div class="admin-card mt-6">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Actions</h3>
            </div>
            
            <div class="admin-card-body space-y-3">
                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                    @csrf
                    <button type="submit" class="admin-btn-success w-full"
                            onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                        <i class="bi bi-check-circle mr-1"></i> Approve Withdrawal
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                    @csrf
                    <button type="submit" class="admin-btn-danger w-full"
                            onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                        <i class="bi bi-x-circle mr-1"></i> Reject Withdrawal
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.withdrawals.cancel', $withdrawal) }}">
                    @csrf
                    <button type="submit" class="admin-btn-secondary w-full"
                            onclick="return confirm('Are you sure you want to cancel this withdrawal?')">
                        <i class="bi bi-slash-circle mr-1"></i> Cancel Withdrawal
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection