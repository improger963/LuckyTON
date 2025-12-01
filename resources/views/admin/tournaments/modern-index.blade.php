@extends('admin.layouts.modern-app')

@section('title', 'Tournaments')
@section('subtitle', 'Manage tournaments')

@section('actions')
<a href="{{ route('admin.tournaments.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
    <i class="bi bi-plus-lg mr-2"></i> Add Tournament
</a>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tournaments Management</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="{{ route('admin.tournaments.index') }}" class="flex space-x-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search tournaments..." 
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
                    <th scope="col" class="px-6 py-3">Tournament</th>
                    <th scope="col" class="px-6 py-3">Game Type</th>
                    <th scope="col" class="px-6 py-3">Prize Pool</th>
                    <th scope="col" class="px-6 py-3">Players</th>
                    <th scope="col" class="px-6 py-3">Dates</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tournaments as $tournament)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $tournament->name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ Str::limit($tournament->description ?? 'No description', 50) }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            {{ ucfirst($tournament->game_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-green-600 dark:text-green-400">
                            ${{ number_format($tournament->prize_pool, 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <span class="font-medium">{{ $tournament->players_count }}</span>
                            <span class="mx-1">/</span>
                            <span>{{ $tournament->max_players }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <div class="font-medium">Reg: {{ $tournament->registration_opens_at->format('M d') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">Start: {{ $tournament->starts_at->format('M d') }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @switch($tournament->status)
                            @case('draft')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    Draft
                                </span>
                                @break
                            @case('registration_open')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Registration Open
                                </span>
                                @break
                            @case('registration_closed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Registration Closed
                                </span>
                                @break
                            @case('in_progress')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
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
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.tournaments.show', $tournament) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.tournaments.edit', $tournament) }}" 
                               class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            @if($tournament->status === 'registration_open')
                                <form method="POST" action="{{ route('admin.tournaments.start', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to start this tournament?')"
                                            title="Start">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </form>
                            @elseif($tournament->status === 'in_progress')
                                <form method="POST" action="{{ route('admin.tournaments.complete', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-blue-500 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to complete this tournament?')"
                                            title="Complete">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            @elseif(in_array($tournament->status, ['draft', 'cancelled']))
                                <form method="POST" action="{{ route('admin.tournaments.enable', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-green-500 hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to enable this tournament?')"
                                            title="Enable">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                </form>
                            @elseif(in_array($tournament->status, ['registration_open', 'draft']))
                                <form method="POST" action="{{ route('admin.tournaments.disable', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to disable this tournament?')"
                                            title="Disable">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </form>
                            @elseif($tournament->status === 'registration_open')
                                <form method="POST" action="{{ route('admin.tournaments.closeRegistration', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-yellow-500 hover:bg-yellow-100 dark:text-yellow-400 dark:hover:bg-yellow-900/30 transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to close registration for this tournament?')"
                                            title="Close Registration">
                                        <i class="bi bi-door-closed"></i>
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors duration-200"
                                        onclick="return confirm('Are you sure you want to delete this tournament?')"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-trophy text-3xl mb-3 block"></i>
                        <p>No tournaments found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($tournaments->hasPages())
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
                Showing <span class="font-medium">{{ $tournaments->firstItem() }}</span> to <span class="font-medium">{{ $tournaments->lastItem() }}</span> of <span class="font-medium">{{ $tournaments->total() }}</span> results
            </div>
            <div>
                {{ $tournaments->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection