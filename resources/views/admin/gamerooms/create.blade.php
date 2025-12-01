@extends('admin.layouts.app')

@section('title', 'Create Game Room')
@section('subtitle', 'Add a new game room')

@section('actions')
<a href="{{ route('admin.gamerooms.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Rooms
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Game Room</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.gamerooms.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="admin-form-label">Room Name</label>
                    <input type="text" name="name" id="name" 
                           value="{{ old('name') }}"
                           class="admin-form-input" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="game_type" class="admin-form-label">Game Type</label>
                    <select name="game_type" id="game_type" class="admin-form-input" required>
                        <option value="">Select Game Type</option>
                        <option value="poker" {{ old('game_type') == 'poker' ? 'selected' : '' }}>
                            Poker
                        </option>
                        <option value="blot" {{ old('game_type') == 'blot' ? 'selected' : '' }}>
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
                           value="{{ old('stake') }}"
                           class="admin-form-input" required>
                    @error('stake')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="max_players" class="admin-form-label">Max Players</label>
                    <input type="number" name="max_players" id="max_players" min="2" max="10"
                           value="{{ old('max_players', 6) }}"
                           class="admin-form-input" required>
                    @error('max_players')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="admin-form-label">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="admin-form-input">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="status" class="admin-form-label">Status</label>
                    <select name="status" id="status" class="admin-form-input" required>
                        <option value="">Select Status</option>
                        <option value="waiting" {{ old('status') == 'waiting' ? 'selected' : '' }}>
                            Waiting
                        </option>
                        <option value="disabled" {{ old('status') == 'disabled' ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
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
                    <i class="bi bi-plus-circle mr-1"></i> Create Room
                </button>
                <a href="{{ route('admin.gamerooms.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection