@extends('admin.layouts.app')

@section('title', 'Tournament Details')
@section('subtitle', 'View tournament information')

@section('actions')
<a href="{{ route('admin.tournaments.index') }}" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Tournaments
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Tournament Info -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tournament #{{ $tournament->id }}</h2>
            </div>
            
            <div class="admin-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="admin-form-label">Name</label>
                        <p class="text-gray-900 dark:text-white font-medium">
                            {{ $tournament->name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Game Type</label>
                        <p class="text-gray-900 dark:text-white">
                            @if($tournament->game_type === 'poker')
                                <span class="admin-badge-primary">Poker</span>
                            @elseif($tournament->game_type === 'blot')
                                <span class="admin-badge-success">Blot</span>
                            @else
                                <span class="admin-badge-secondary">{{ ucfirst($tournament->game_type) }}</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Buy-in</label>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($tournament->buy_in, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Prize Pool</label>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($tournament->prize_pool, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Players</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $tournament->current_players_count }} / {{ $tournament->max_players }}
                            ({{ $tournament->available_spots }} spots available)
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Status</label>
                        @switch($tournament->status)
                            @case('draft')
                                <span class="admin-badge-secondary text-lg">Draft</span>
                                @break
                            @case('registration_open')
                                <span class="admin-badge-success text-lg">Registration Open</span>
                                @break
                            @case('registration_closed')
                                <span class="admin-badge-warning text-lg">Registration Closed</span>
                                @break
                            @case('in_progress')
                                <span class="admin-badge-primary text-lg">In Progress</span>
                                @break
                            @case('completed')
                                <span class="admin-badge-success text-lg">Completed</span>
                                @break
                            @case('cancelled')
                                <span class="admin-badge-danger text-lg">Cancelled</span>
                                @break
                        @endswitch
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Registration Opens</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $tournament->registration_opens_at->format('M d, Y H:i:s') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Starts</label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $tournament->starts_at->format('M d, Y H:i:s') }}
                        </p>
                    </div>
                </div>
                
                @if($tournament->description)
                <div class="mt-6">
                    <label class="admin-form-label">Description</label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $tournament->description }}
                    </p>
                </div>
                @endif
                                        
                @if($tournament->status === 'cancelled' && $tournament->cancellation_reason)
                <div class="mt-6">
                    <label class="admin-form-label">Cancellation Reason</label>
                    <p class="text-gray-900 dark:text-white">
                        {{ $tournament->cancellation_reason }}
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
                <h3 class="font-semibold text-gray-900 dark:text-white">Players ({{ $tournament->current_players_count }})</h3>
            </div>
            
            <div class="admin-card-body">
                @if($tournament->players->count() > 0)
                    <div class="space-y-3">
                        @foreach($tournament->players as $player)
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
                        No players registered for this tournament
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
            <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="admin-btn-primary">
                <i class="bi bi-pencil mr-1"></i> Edit Tournament
            </a>
            
            <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn-danger"
                        onclick="return confirm('Are you sure you want to delete this tournament?')">
                    <i class="bi bi-trash mr-1"></i> Delete Tournament
                </button>
            </form>
        </div>
    </div>
</div>
@endsection