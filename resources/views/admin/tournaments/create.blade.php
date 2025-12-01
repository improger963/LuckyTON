@extends('admin.layouts.modern-app')

@section('title', 'Create Tournament')
@section('subtitle', 'Add a new tournament')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.tournaments.index') }}" icon="bi bi-arrow-left">
    Back to Tournaments
</x-admin.button>
@endsection

@section('content')
<x-admin.card title="Create New Tournament" subtitle="Fill in the form below to create a new tournament">
    <form method="POST" action="{{ route('admin.tournaments.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-admin.form-input 
                label="Tournament Name" 
                name="name" 
                value="{{ old('name') }}" 
                placeholder="Enter tournament name" 
                required="true" 
                error="true" />
            
            <div>
                <label for="game_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Game Type
                </label>
                <select name="game_type" id="game_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
                    <option value="">Select Game Type</option>
                    <option value="poker" {{ old('game_type') == 'poker' ? 'selected' : '' }}>
                        Poker
                    </option>
                    <option value="blot" {{ old('game_type') == 'blot' ? 'selected' : '' }}>
                        Blot
                    </option>
                </select>
                @error('game_type')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <x-admin.form-input 
                label="Buy-in Amount ($)" 
                name="buy_in" 
                type="number" 
                step="0.01" 
                min="0"
                value="{{ old('buy_in') }}" 
                placeholder="0.00" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Prize Pool ($)" 
                name="prize_pool" 
                type="number" 
                step="0.01" 
                min="0"
                value="{{ old('prize_pool') }}" 
                placeholder="0.00" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Max Players" 
                name="max_players" 
                type="number" 
                min="2" 
                max="100"
                value="{{ old('max_players', 8) }}" 
                placeholder="Enter max players" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Registration Opens" 
                name="registration_opens_at" 
                type="datetime-local" 
                value="{{ old('registration_opens_at') }}" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Tournament Starts" 
                name="starts_at" 
                type="datetime-local" 
                value="{{ old('starts_at') }}" 
                required="true" 
                error="true" />
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
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
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex items-center">
            <x-admin.button variant="primary" icon="bi bi-plus-circle" type="submit">
                Create Tournament
            </x-admin.button>
            <x-admin.button variant="secondary" href="{{ route('admin.tournaments.index') }}" class="ml-2">
                Cancel
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection