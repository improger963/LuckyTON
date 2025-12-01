@extends('admin.layouts.modern-app')

@section('title', 'Game Rooms')
@section('subtitle', 'Manage game rooms')

@section('actions')
<a href="{{ route('admin.gamerooms.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
    <i class="bi bi-plus-lg mr-2"></i> Add Room
</a>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Game Rooms Management</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.gamerooms.index') }}" class="flex space-x-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search rooms..." 
                               class="w-full md:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded-lg transition-colors duration-200">
                        <i class="bi bi-funnel"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Room</th>
                    <th scope="col" class="px-6 py-3">Game Type</th>
                    <th scope="col" class="px-6 py-3">Stake</th>
                    <th scope="col" class="px-6 py-3">Players</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $room->name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $room->description ?? 'No description' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ ucfirst($room->game_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-green-600 dark:text-green-400">
                            ${{ number_format($room->stake, 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <span class="font-medium">{{ $room->players_count }}</span>
                            <span class="mx-1">/</span>
                            <span>{{ $room->max_players }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @switch($room->status)
                            @case('waiting')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Waiting
                                </span>
                                @break
                            @case('cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    Cancelled
                                </span>
                                @break
                            @case('in_progress')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    In Progress
                                </span>
                                @break
                            @case('finished')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Finished
                                </span>
                                @break
                        @endswitch
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.gamerooms.show', $room) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.gamerooms.edit', $room) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            @if($room->status === 'waiting')
                                <form method="POST" action="{{ route('admin.gamerooms.start', $room) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to start this game room?')"
                                            title="Start">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </form>
                            @elseif($room->status === 'in_progress')
                                <form method="POST" action="{{ route('admin.gamerooms.complete', $room) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-blue-500 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to complete this game room?')"
                                            title="Complete">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            @elseif($room->status === 'cancelled')
                                <form method="POST" action="{{ route('admin.gamerooms.enable', $room) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to enable this game room?')"
                                            title="Enable">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                </form>
                            @elseif($room->status === 'waiting')
                                <form method="POST" action="{{ route('admin.gamerooms.disable', $room) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to disable this game room?')"
                                            title="Disable">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.gamerooms.destroy', $room) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors duration-200"
                                        onclick="return confirm('Are you sure you want to delete this game room?')"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-controller text-3xl mb-3 block"></i>
                        <p>No game rooms found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($rooms->hasPages())
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
                Showing <span class="font-medium">{{ $rooms->firstItem() }}</span> to <span class="font-medium">{{ $rooms->lastItem() }}</span> of <span class="font-medium">{{ $rooms->total() }}</span> results
            </div>
            <div>
                {{ $rooms->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection