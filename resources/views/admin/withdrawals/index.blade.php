@extends('admin.layouts.app')

@section('title', 'Withdrawals')
@section('subtitle', 'Manage withdrawal requests')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Withdrawal Requests</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="flex space-x-2">
                    <select name="status" class="admin-form-input">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                            Failed
                        </option>
                    </select>
                    <button type="submit" class="admin-btn-primary">
                        Filter
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">
                            {{ $withdrawal->id }}
                        </td>
                        <td>
                            @if($withdrawal->wallet && $withdrawal->wallet->user)
                                {{ $withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="font-medium">
                            ${{ number_format($withdrawal->amount, 2) }}
                        </td>
                        <td>
                            @if(isset($withdrawal->metadata['method']))
                                {{ ucfirst($withdrawal->metadata['method']) }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            {{ $withdrawal->created_at->format('M d, Y H:i') }}
                        </td>
                        <td>
                            @switch($withdrawal->status)
                                @case('pending')
                                    <span class="admin-badge-warning">Pending</span>
                                    @break
                                @case('completed')
                                    <span class="admin-badge-success">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="admin-badge-secondary">Cancelled</span>
                                    @break
                                @case('failed')
                                    <span class="admin-badge-danger">Failed</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($withdrawal->status === 'pending')
                                    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                                        @csrf
                                        <button type="submit" class="admin-btn-success text-sm py-1 px-3"
                                                onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                                        @csrf
                                        <button type="submit" class="admin-btn-danger text-sm py-1 px-3"
                                                onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-cash-coin text-2xl mb-2 block"></i>
                            No withdrawal requests found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($withdrawals->hasPages())
        <div class="mt-6">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection