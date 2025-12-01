@extends('admin.layouts.modern-app')

@section('title', 'Edit Game Room')
@section('subtitle', 'Modify game room settings')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.gamerooms.index') }}" icon="bi bi-arrow-left">
    Back to Rooms
</x-admin.button>
@endsection

@section('content')
<x-admin.card title="Edit Game Room" subtitle="Update game room settings">
    <form method="POST" action="{{ route('admin.gamerooms.update', $room) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-admin.form-input 
                label="Room Name" 
                name="name" 
                value="{{ old('name', $room->name) }}" 
                placeholder="Enter room name" 
                required="true" 
                error="true" />
            
            <div>
                <label for="game_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Game Type
                </label>
                <select name="game_type" id="game_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
                    <option value="poker" {{ old('game_type', $room->game_type) == 'poker' ? 'selected' : '' }}>
                        Poker
                    </option>
                    <option value="blot" {{ old('game_type', $room->game_type) == 'blot' ? 'selected' : '' }}>
                        Blot
                    </option>
                </select>
                @error('game_type')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <x-admin.form-input 
                label="Stake Amount ($)" 
                name="stake" 
                type="number" 
                step="0.01" 
                min="0"
                value="{{ old('stake', $room->stake) }}" 
                placeholder="0.00" 
                required="true" 
                error="true" />
            
            <x-admin.form-input 
                label="Max Players" 
                name="max_players" 
                type="number" 
                min="2" 
                max="10"
                value="{{ old('max_players', $room->max_players) }}" 
                placeholder="Enter max players" 
                required="true" 
                error="true" />
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">{{ old('description', $room->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" required>
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
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex items-center">
            <x-admin.button variant="primary" icon="bi bi-save" type="submit">
                Save Changes
            </x-admin.button>
            <x-admin.button variant="secondary" href="{{ route('admin.gamerooms.index') }}" class="ml-2">
                Cancel
            </x-admin.button>
        </div>
    </form>
</x-admin.card>

<!-- Status Management -->
<x-admin.card title="Room Status" subtitle="Manage game room status" class="mt-6">
    <div class="flex flex-wrap gap-3">
        @if($room->status === 'waiting')
            <form method="POST" action="{{ route('admin.gamerooms.start', $room) }}">
                @csrf
                <x-admin.button variant="primary" icon="bi bi-play-circle" type="submit">
                    Start Game
                </x-admin.button>
            </form>
        @endif
        
        @if(in_array($room->status, ['waiting', 'in_progress']))
            <form method="POST" action="{{ route('admin.gamerooms.cancel', $room) }}">
                @csrf
                <x-admin.button variant="danger" icon="bi bi-slash-circle" type="submit"
                    onclick="return confirm('Are you sure you want to cancel this game room?')">
                    Cancel Room
                </x-admin.button>
            </form>
        @endif
        
        @if($room->status === 'in_progress')
            <form method="POST" action="{{ route('admin.gamerooms.complete', $room) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-check-circle" type="submit">
                    Complete Game
                </x-admin.button>
            </form>
        @endif
        
        @if($room->status === 'disabled')
            <form method="POST" action="{{ route('admin.gamerooms.enable', $room) }}">
                @csrf
                <x-admin.button variant="success" icon="bi bi-play-circle" type="submit">
                    Enable Room
                </x-admin.button>
            </form>
        @endif
        
        @if($room->status === 'waiting')
            <form method="POST" action="{{ route('admin.gamerooms.disable', $room) }}">
                @csrf
                <x-admin.button variant="secondary" icon="bi bi-lock" type="submit">
                    Disable Room
                </x-admin.button>
            </form>
        @endif
    </div>
    
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Current Status: 
            @switch($room->status)
                @case('waiting')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        Waiting
                    </span>
                    @break
                @case('disabled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        Disabled
                    </span>
                    @break
                @case('in_progress')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        In Progress
                    </span>
                    @break
                @case('completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Completed
                    </span>
                    @break
            @endswitch
        </p>
    </div>
</x-admin.card>
@endsection