@extends('admin.layouts.modern-app')

@section('title', 'Edit Tournament')
@section('subtitle', 'Modify tournament settings')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.tournaments.index') }}" icon="bi bi-arrow-left">
    Back to Tournaments
</x-admin.button>
@endsection

@section('content')
<x-admin.card title="Edit Tournament" subtitle="Update tournament settings">
    <form method="POST" action="{{ route('admin.tournaments.update', $tournament) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-admin.form-input 
                label="Tournament Name" 
                name="name" 
                value="{{ old('name', $tournament->name) }}" 
                placeholder="Enter tournament name" 
                required="true" 
                error="true" />
            
            <div>
                <label for="game_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Game Type
                </label>
                <select name="game_type" id="game_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
                    <option value="poker" {{ old('game_type', $tournament->game_type) == 'poker' ? 'selected' : '' }}>
                        Poker
                    </option>
                    <option value="blot" {{ old('game_type', $tournament->game_type) == 'blot' ? 'selected' : '' }}>
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
                value="{{ old('buy_in', $tournament->buy_in) }}" 
                placeholder="0.00" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Prize Pool ($)" 
                name="prize_pool" 
                type="number" 
                step="0.01" 
                min="0"
                value="{{ old('prize_pool', $tournament->prize_pool) }}" 
                placeholder="0.00" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Max Players" 
                name="max_players" 
                type="number" 
                min="2" 
                max="100"
                value="{{ old('max_players', $tournament->max_players) }}" 
                placeholder="Enter max players" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Registration Opens" 
                name="registration_opens_at" 
                type="datetime-local" 
                value="{{ old('registration_opens_at', $tournament->registration_opens_at ? $tournament->registration_opens_at->format('Y-m-d\TH:i') : '') }}" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Tournament Starts" 
                name="starts_at" 
                type="datetime-local" 
                value="{{ old('starts_at', $tournament->starts_at ? $tournament->starts_at->format('Y-m-d\TH:i') : '') }}" 
                required="true" 
                error="true" />
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">{{ old('description', $tournament->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
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
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex items-center">
            <x-admin.button variant="primary" icon="bi bi-save" type="submit">
                Save Changes
            </x-admin.button>
            <x-admin.button variant="secondary" href="{{ route('admin.tournaments.index') }}" class="ml-2">
                Cancel
            </x-admin.button>
        </div>
    </form>
</x-admin.card>

<!-- Status Management -->
<x-admin.card title="Tournament Status" subtitle="Manage tournament status" class="mt-6">
    <div class="flex flex-wrap gap-3">
        @if($tournament->status === 'draft')
            <form method="POST" action="{{ route('admin.tournaments.enable', $tournament) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-play-circle" type="submit">
                    Open Registration
                </x-admin.button>
            </form>
        @endif
        
        @if($tournament->status === 'registration_open')
            <form method="POST" action="{{ route('admin.tournaments.closeRegistration', $tournament) }}">
                @csrf
                <x-admin.button variant="warning" icon="bi bi-lock" type="submit">
                    Close Registration
                </x-admin.button>
            </form>
        @endif
        
        @if(in_array($tournament->status, ['registration_open', 'registration_closed']))
            <form method="POST" action="{{ route('admin.tournaments.start', $tournament) }}">
                @csrf
                <x-admin.button variant="primary" icon="bi bi-play-circle" type="submit">
                    Start Tournament
                </x-admin.button>
            </form>
        @endif
        
        @if($tournament->status === 'in_progress')
            <form method="POST" action="{{ route('admin.tournaments.complete', $tournament) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-check-circle" type="submit">
                    Complete Tournament
                </x-admin.button>
            </form>
        @endif
        
        @if(in_array($tournament->status, ['draft', 'registration_open', 'registration_closed', 'in_progress']))
            <form method="POST" action="{{ route('admin.tournaments.cancel', $tournament) }}">
                @csrf
                <x-admin.button variant="danger" icon="bi bi-slash-circle" type="submit"
                    onclick="return confirm('Are you sure you want to cancel this tournament?')">
                    Cancel Tournament
                </x-admin.button>
            </form>
        @endif
        
        @if(in_array($tournament->status, ['draft', 'cancelled']))
            <form method="POST" action="{{ route('admin.tournaments.enable', $tournament) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-unlock" type="submit">
                    Enable Tournament
                </x-admin.button>
            </form>
        @endif
        
        @if(in_array($tournament->status, ['registration_open', 'draft']))
            <form method="POST" action="{{ route('admin.tournaments.disable', $tournament) }}">
                @csrf
                <x-admin.button variant="secondary" icon="bi bi-lock" type="submit">
                    Disable Tournament
                </x-admin.button>
            </form>
        @endif
    </div>
    
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Current Status: 
            @switch($tournament->status)
                @case('draft')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        Draft
                    </span>
                    @break
                @case('registration_open')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Registration Open
                    </span>
                    @break
                @case('registration_closed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        Registration Closed
                    </span>
                    @break
                @case('in_progress')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        In Progress
                    </span>
                    @break
                @case('completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        Completed
                    </span>
                    @break
                @case('cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Cancelled
                    </span>
                    @break
            @endswitch
        </p>
    </div>
</x-admin.card>
@endsection