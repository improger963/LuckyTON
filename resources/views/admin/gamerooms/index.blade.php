@extends('admin.layouts.app')

@section('title', 'Game Rooms')
@section('subtitle', 'Manage game rooms')

@section('actions')
<a href="{{ route('admin.gamerooms.create') }}" class="admin-btn-primary">
    <i class="bi bi-plus-lg mr-1"></i> Create Room
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Game Rooms</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.gamerooms.index') }}" class="flex space-x-2">
                    <select name="status" class="admin-form-input">
                        <option value="">All Statuses</option>
                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>
                            Waiting
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
                        <th>Stake</th>
                        <th>Players</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">
                            {{ $room->id }}
                        </td>
                        <td>
                            {{ $room->name }}
                        </td>
                        <td>
                            @if($room->game_type === 'poker')
                                <span class="admin-badge-primary">Poker</span>
                            @elseif($room->game_type === 'blot')
                                <span class="admin-badge-success">Blot</span>
                            @else
                                <span class="admin-badge-secondary">{{ ucfirst($room->game_type) }}</span>
                            @endif
                        </td>
                        <td class="font-medium">
                            ${{ number_format($room->stake, 2) }}
                        </td>
                        <td>
                            {{ $room->current_players_count }} / {{ $room->max_players }}
                        </td>
                        <td>
                            @switch($room->status)
                                @case('waiting')
                                    <span class="admin-badge-warning">Waiting</span>
                                    @break
                                @case('in_progress')
                                    <span class="admin-badge-primary">In Progress</span>
                                    @break
                                @case('completed')
                                    <span class="admin-badge-success">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="admin-badge-secondary">Cancelled</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.gamerooms.show', $room) }}" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.gamerooms.edit', $room) }}" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.gamerooms.destroy', $room) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-danger text-sm py-1 px-3"
                                            onclick="return confirm('Are you sure you want to delete this game room?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-controller text-2xl mb-2 block"></i>
                            No game rooms found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($rooms->hasPages())
        <div class="mt-6">
            {{ $rooms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection