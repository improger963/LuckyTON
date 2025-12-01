@extends('admin.layouts.app')

@section('title', 'Edit Game Room')
@section('subtitle', 'Modify game room settings')

@section('actions')
<a href="{{ route('admin.gamerooms.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Rooms
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Game Room</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.gamerooms.update', $room) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="admin-form-label">Room Name</label>
                    <input type="text" name="name" id="name" 
                           value="{{ old('name', $room->name) }}"
                           class="admin-form-input" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="game_type" class="admin-form-label">Game Type</label>
                    <select name="game_type" id="game_type" class="admin-form-input" required>
                        <option value="poker" {{ old('game_type', $room->game_type) == 'poker' ? 'selected' : '' }}>
                            Poker
                        </option>
                        <option value="blot" {{ old('game_type', $room->game_type) == 'blot' ? 'selected' : '' }}>
                            Blot
                        </option>
                    </select>
                    @error('game_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="stake" class="admin-form-label">Stake Amount ($)</label>
                    <input type="number" name="stake" id="stake" step="0.01" min="0"
                           value="{{ old('stake', $room->stake) }}"
                           class="admin-form-input" required>
                    @error('stake')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="max_players" class="admin-form-label">Max Players</label>
                    <input type="number" name="max_players" id="max_players" min="2" max="10"
                           value="{{ old('max_players', $room->max_players) }}"
                           class="admin-form-input" required>
                    @error('max_players')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="admin-form-label">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="admin-form-input">{{ old('description', $room->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="status" class="admin-form-label">Status</label>
                    <select name="status" id="status" class="admin-form-input" required>
                        <option value="waiting" {{ old('status', $room->status) == 'waiting' ? 'selected' : '' }}>
                            Waiting
                        </option>
                        <option value="disabled" {{ old('status', $room->status) == 'disabled' ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="in_progress" {{ old('status', $room->status) == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ old('status', $room->status) == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="admin-btn-primary">
                    <i class="bi bi-save mr-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.gamerooms.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Status Management -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Room Status</h2>
    </div>
    
    <div class="admin-card-body">
        <div class="flex flex-wrap gap-3">
            @if($room->status === 'waiting')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-primary">
                        <i class="bi bi-play-circle mr-1"></i> Start Game
                    </button>
                </form>
            @endif
            
            @if(in_array($room->status, ['waiting', 'in_progress']))
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-danger"
                            onclick="return confirm('Are you sure you want to cancel this game room?')">
                        <i class="bi bi-slash-circle mr-1"></i> Cancel Room
                    </button>
                </form>
            @endif
            
            @if($room->status === 'in_progress')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-check-circle mr-1"></i> Complete Game
                    </button>
                </form>
            @endif
            
            @if($room->status === 'disabled')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-play-circle mr-1"></i> Enable Room
                    </button>
                </form>
            @endif
        </div>
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Current Status: 
                @switch($room->status)
                    @case('waiting')
                        <span class="admin-badge-secondary">Waiting</span>
                        @break
                    @case('disabled')
                        <span class="admin-badge-warning">Disabled</span>
                        @break
                    @case('in_progress')
                        <span class="admin-badge-primary">In Progress</span>
                        @break
                    @case('completed')
                        <span class="admin-badge-success">Completed</span>
                        @break
                @endswitch
            </p>
        </div>
    </div>
</div>
@endsection