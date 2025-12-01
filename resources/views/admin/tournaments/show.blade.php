@extends('admin.layouts.modern-app')

@section('title', 'Tournament Details')
@section('subtitle', 'View tournament information')

@section('actions')
<x-admin.button variant="secondary" href="{{ route('admin.tournaments.index') }}" icon="bi bi-arrow-left">
    Back to Tournaments
</x-admin.button>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Tournament Info -->
    <div class="lg:col-span-2">
        <x-admin.card title="Tournament #{{ $tournament->id }}" subtitle="Detailed information about this tournament">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Name
                    </label>
                    <p class="text-gray-900 dark:text-white font-medium">
                        {{ $tournament->name }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Game Type
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        @if($tournament->game_type === 'poker')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Poker
                            </span>
                        @elseif($tournament->game_type === 'blot')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Blot
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ ucfirst($tournament->game_type) }}
                            </span>
                        @endif
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Buy-in
                    </label>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        ${{ number_format($tournament->buy_in, 2) }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Prize Pool
                    </label>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($tournament->prize_pool, 2) }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Players
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $tournament->current_players_count }} / {{ $tournament->max_players }}
                        ({{ $tournament->available_spots }} spots available)
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status
                    </label>
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
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Registration Opens
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $tournament->registration_opens_at->format('M d, Y H:i:s') }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Starts
                    </label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $tournament->starts_at->format('M d, Y H:i:s') }}
                    </p>
                </div>
            </div>
            
            @if($tournament->description)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Description
                </label>
                <p class="text-gray-900 dark:text-white">
                    {{ $tournament->description }}
                </p>
            </div>
            @endif
                                    
            @if($tournament->status === 'cancelled' && $tournament->cancellation_reason)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Cancellation Reason
                </label>
                <p class="text-gray-900 dark:text-white">
                    {{ $tournament->cancellation_reason }}
                </p>
            </div>
            @endif
        </x-admin.card>
    </div>
    
    <!-- Players -->
    <div class="lg:col-span-1">
        <x-admin.card title="Players ({{ $tournament->current_players_count }})" subtitle="Users registered for this tournament">
            @if($tournament->players->count() > 0)
                <div class="space-y-3">
                    @foreach($tournament->players as $player)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center mr-2 text-white text-xs font-bold">
                                {{ substr($player->username ?? $player->email, 0, 1) }}
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
                    No players registered for this tournament
                </p>
            @endif
        </x-admin.card>
    </div>
</div>

<!-- Actions -->
<x-admin.card class="mt-6">
    <div class="flex flex-wrap gap-3">
        <x-admin.button variant="primary" href="{{ route('admin.tournaments.edit', $tournament) }}" icon="bi bi-pencil">
            Edit Tournament
        </x-admin.button>
        
        <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}">
            @csrf
            @method('DELETE')
            <x-admin.button variant="danger" icon="bi bi-trash" type="submit"
                onclick="return confirm('Are you sure you want to delete this tournament?')">
                Delete Tournament
            </x-admin.button>
        </form>
    </div>
</x-admin.card>
@endsection