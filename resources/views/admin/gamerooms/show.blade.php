@extends('admin.layouts.app')

@section('title', 'Game Room Details')
@section('subtitle', 'View game room information')

@section('actions')
<a href="{{ route('admin.gamerooms.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Rooms
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Room Info -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Game Room #{{ $room->id }}</h2>
            </div>
            
            <div class="admin-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="admin-form-label">Name</label>
                        <p class="text-gray-900 dark:text-white font-medium">
                            {{ $room->name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Game Type</label>
                        <p class="text-gray-900 dark:text-white">
                            @if($room->game_type === 'poker')
                                <span class="admin-badge-primary">Poker</span>
                            @elseif($room->game_type === 'blot')
                                <span class="admin-badge-success">Blot</span>
                            @else
                                <span class="admin-badge-secondary">{{ ucfirst($room->game_type) }}</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Stake</label>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($room->stake, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Players</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $room->current_players_count }} / {{ $room->max_players }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Status</label>
                        @switch($room->status)
                            @case('waiting')
                                <span class="admin-badge-warning text-lg">Waiting</span>
                                @break
                            @case('disabled')
                                <span class="admin-badge-secondary text-lg">Disabled</span>
                                @break
                            @case('in_progress')
                                <span class="admin-badge-primary text-lg">In Progress</span>
                                @break
                            @case('completed')
                                <span class="admin-badge-success text-lg">Completed</span>
                                @break
                        @endswitch
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Created</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $room->created_at->format('M d, Y H:i:s') }}
                        </p>
                    </div>
                </div>
                
                @if($room->description)
                <div class="mt-6">
                    <label class="admin-form-label">Description</label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $room->description }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Players -->
    <div class="lg:col-span-1">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Players ({{ $room->current_players_count }})</h3>
            </div>
            
            <div class="admin-card-body">
                @if($room->players->count() > 0)
                    <div class="space-y-3">
                        @foreach($room->players as $player)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-2">
                                    <i class="bi bi-person text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">
                                        {{ $player->username ?? $player->email }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        ID: {{ $player->id }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.users.show', $player) }}" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No players in this room
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="admin-card mt-6">
    <div class="admin-card-body">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.gamerooms.edit', $room) }}" class="admin-btn-primary">
                <i class="bi bi-pencil mr-1"></i> Edit Room
            </a>
            
            <form method="POST" action="{{ route('admin.gamerooms.destroy', $room) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn-danger"
                        onclick="return confirm('Are you sure you want to delete this game room?')">
                    <i class="bi bi-trash mr-1"></i> Delete Room
                </button>
            </form>
        </div>
    </div>
</div>
@endsection