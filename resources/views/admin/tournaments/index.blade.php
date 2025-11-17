@extends('admin.layouts.app')

@section('title', 'Tournaments')
@section('subtitle', 'Manage tournaments')

@section('actions')
<a href="{{ route('admin.tournaments.create') }}" class="admin-btn-primary">
    <i class="bi bi-plus-lg mr-1"></i> Create Tournament
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tournaments</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.tournaments.index') }}" class="flex space-x-2">
                    <select name="status" class="admin-form-input">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                        <option value="registration_open" {{ request('status') == 'registration_open' ? 'selected' : '' }}>
                            Registration Open
                        </option>
                        <option value="registration_closed" {{ request('status') == 'registration_closed' ? 'selected' : '' }}>
                            Registration Closed
                        </option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                    </select>
                    <select name="game_type" class="admin-form-input">
                        <option value="">All Types</option>
                        <option value="poker" {{ request('game_type') == 'poker' ? 'selected' : '' }}>
                            Poker
                        </option>
                        <option value="blot" {{ request('game_type') == 'blot' ? 'selected' : '' }}>
                            Blot
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
                        <th>Name</th>
                        <th>Game Type</th>
                        <th>Buy-in</th>
                        <th>Prize Pool</th>
                        <th>Players</th>
                        <th>Status</th>
                        <th>Starts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tournaments as $tournament)
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">
                            {{ $tournament->id }}
                        </td>
                        <td>
                            {{ $tournament->name }}
                        </td>
                        <td>
                            @if($tournament->game_type === 'poker')
                                <span class="admin-badge-primary">Poker</span>
                            @elseif($tournament->game_type === 'blot')
                                <span class="admin-badge-success">Blot</span>
                            @else
                                <span class="admin-badge-secondary">{{ ucfirst($tournament->game_type) }}</span>
                            @endif
                        </td>
                        <td class="font-medium">
                            ${{ number_format($tournament->buy_in, 2) }}
                        </td>
                        <td class="font-medium">
                            ${{ number_format($tournament->prize_pool, 2) }}
                        </td>
                        <td>
                            {{ $tournament->current_players_count }} / {{ $tournament->max_players }}
                        </td>
                        <td>
                            @switch($tournament->status)
                                @case('draft')
                                    <span class="admin-badge-secondary">Draft</span>
                                    @break
                                @case('registration_open')
                                    <span class="admin-badge-success">Registration Open</span>
                                    @break
                                @case('registration_closed')
                                    <span class="admin-badge-warning">Registration Closed</span>
                                    @break
                                @case('in_progress')
                                    <span class="admin-badge-primary">In Progress</span>
                                    @break
                                @case('completed')
                                    <span class="admin-badge-success">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="admin-badge-danger">Cancelled</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            {{ $tournament->starts_at->format('M d, Y H:i') }}
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.tournaments.show', $tournament) }}" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.tournaments.edit', $tournament) }}" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-danger text-sm py-1 px-3"
                                            onclick="return confirm('Are you sure you want to delete this tournament?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-trophy text-2xl mb-2 block"></i>
                            No tournaments found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tournaments->hasPages())
        <div class="mt-6">
            {{ $tournaments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection