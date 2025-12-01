@extends('admin.layouts.app')

@section('title', 'Edit Tournament')
@section('subtitle', 'Modify tournament settings')

@section('actions')
<a href="{{ route('admin.tournaments.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Tournaments
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Tournament</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.tournaments.update', $tournament) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="admin-form-label">Tournament Name</label>
                    <input type="text" name="name" id="name" 
                           value="{{ old('name', $tournament->name) }}"
                           class="admin-form-input" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="game_type" class="admin-form-label">Game Type</label>
                    <select name="game_type" id="game_type" class="admin-form-input" required>
                        <option value="poker" {{ old('game_type', $tournament->game_type) == 'poker' ? 'selected' : '' }}>
                            Poker
                        </option>
                        <option value="blot" {{ old('game_type', $tournament->game_type) == 'blot' ? 'selected' : '' }}>
                            Blot
                        </option>
                    </select>
                    @error('game_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="buy_in" class="admin-form-label">Buy-in Amount ($)</label>
                    <input type="number" name="buy_in" id="buy_in" step="0.01" min="0"
                           value="{{ old('buy_in', $tournament->buy_in) }}"
                           class="admin-form-input" required>
                    @error('buy_in')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="prize_pool" class="admin-form-label">Prize Pool ($)</label>
                    <input type="number" name="prize_pool" id="prize_pool" step="0.01" min="0"
                           value="{{ old('prize_pool', $tournament->prize_pool) }}"
                           class="admin-form-input" required>
                    @error('prize_pool')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="max_players" class="admin-form-label">Max Players</label>
                    <input type="number" name="max_players" id="max_players" min="2" max="100"
                           value="{{ old('max_players', $tournament->max_players) }}"
                           class="admin-form-input" required>
                    @error('max_players')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="registration_opens_at" class="admin-form-label">Registration Opens</label>
                    <input type="datetime-local" name="registration_opens_at" id="registration_opens_at"
                           value="{{ old('registration_opens_at', $tournament->registration_opens_at ? $tournament->registration_opens_at->format('Y-m-d\TH:i') : '') }}"
                           class="admin-form-input" required>
                    @error('registration_opens_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="starts_at" class="admin-form-label">Tournament Starts</label>
                    <input type="datetime-local" name="starts_at" id="starts_at"
                           value="{{ old('starts_at', $tournament->starts_at ? $tournament->starts_at->format('Y-m-d\TH:i') : '') }}"
                           class="admin-form-input" required>
                    @error('starts_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="admin-form-label">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="admin-form-input">{{ old('description', $tournament->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="status" class="admin-form-label">Status</label>
                    <select name="status" id="status" class="admin-form-input" required>
                        <option value="draft" {{ old('status', $tournament->status) == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                        <option value="registration_open" {{ old('status', $tournament->status) == 'registration_open' ? 'selected' : '' }}>
                            Registration Open
                        </option>
                        <option value="registration_closed" {{ old('status', $tournament->status) == 'registration_closed' ? 'selected' : '' }}>
                            Registration Closed
                        </option>
                        <option value="in_progress" {{ old('status', $tournament->status) == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ old('status', $tournament->status) == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="cancelled" {{ old('status', $tournament->status) == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
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
                <a href="{{ route('admin.tournaments.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Status Management -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tournament Status</h2>
    </div>
    
    <div class="admin-card-body">
        <div class="flex flex-wrap gap-3">
            @if($tournament->status === 'draft')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-play-circle mr-1"></i> Open Registration
                    </button>
                </form>
            @endif
            
            @if($tournament->status === 'registration_open')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-warning">
                        <i class="bi bi-lock mr-1"></i> Close Registration
                    </button>
                </form>
            @endif
            
            @if(in_array($tournament->status, ['registration_open', 'registration_closed']))
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-primary">
                        <i class="bi bi-play-circle mr-1"></i> Start Tournament
                    </button>
                </form>
            @endif
            
            @if($tournament->status === 'in_progress')
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-check-circle mr-1"></i> Complete Tournament
                    </button>
                </form>
            @endif
            
            @if(in_array($tournament->status, ['draft', 'registration_open', 'registration_closed', 'in_progress']))
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="admin-btn-danger"
                            onclick="return confirm('Are you sure you want to cancel this tournament?')">
                        <i class="bi bi-slash-circle mr-1"></i> Cancel Tournament
                    </button>
                </form>
            @endif
        </div>
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Current Status: 
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
            </p>
        </div>
    </div>
</div>
@endsection