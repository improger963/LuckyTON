@extends('admin.layouts.app')

@section('title', 'Create Tournament')
@section('subtitle', 'Add a new tournament')

@section('actions')
<a href="{{ route('admin.tournaments.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Tournaments
</a>
@endsection

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Tournament</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.tournaments.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="admin-form-label">Tournament Name</label>
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
                    <label for="buy_in" class="admin-form-label">Buy-in Amount ($)</label>
                    <input type="number" name="buy_in" id="buy_in" step="0.01" min="0"
                           value="{{ old('buy_in') }}"
                           class="admin-form-input" required>
                    @error('buy_in')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="prize_pool" class="admin-form-label">Prize Pool ($)</label>
                    <input type="number" name="prize_pool" id="prize_pool" step="0.01" min="0"
                           value="{{ old('prize_pool') }}"
                           class="admin-form-input" required>
                    @error('prize_pool')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="max_players" class="admin-form-label">Max Players</label>
                    <input type="number" name="max_players" id="max_players" min="2" max="100"
                           value="{{ old('max_players', 8) }}"
                           class="admin-form-input" required>
                    @error('max_players')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="registration_opens_at" class="admin-form-label">Registration Opens</label>
                    <input type="datetime-local" name="registration_opens_at" id="registration_opens_at"
                           value="{{ old('registration_opens_at') }}"
                           class="admin-form-input" required>
                    @error('registration_opens_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="starts_at" class="admin-form-label">Tournament Starts</label>
                    <input type="datetime-local" name="starts_at" id="starts_at"
                           value="{{ old('starts_at') }}"
                           class="admin-form-input" required>
                    @error('starts_at')
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
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                        <option value="registration_open" {{ old('status') == 'registration_open' ? 'selected' : '' }}>
                            Registration Open
                        </option>
                        <option value="registration_closed" {{ old('status') == 'registration_closed' ? 'selected' : '' }}>
                            Registration Closed
                        </option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>
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
                    <i class="bi bi-plus-circle mr-1"></i> Create Tournament
                </button>
                <a href="{{ route('admin.tournaments.index') }}" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection