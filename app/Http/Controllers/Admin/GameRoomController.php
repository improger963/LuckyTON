<?php

namespace App\Http\Controllers\Admin;

use App\Models\GameRoom;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameRoomController extends Controller
{
    /**
     * Display game rooms listing
     */
    public function index(): View
    {
        $rooms = GameRoom::withCount('players')
            ->orderBy('game_type')
            ->orderBy('stake')
            ->paginate(15);

        return view('admin.gamerooms.index', ['rooms' => $rooms]);
    }

    /**
     * Show game room creation form
     */
    public function create(): View
    {
        $room = new GameRoom();

        return view('admin.gamerooms.create', ['room' => $room]);
    }

    /**
     * Store new game room
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'game_type' => ['required', 'in:poker,blot'],
            'status' => ['required', 'in:waiting,disabled,in_progress,completed'],
            'stake' => ['required', 'numeric', 'min:0'],
            'max_players' => ['required', 'integer', 'min:2', 'max:10'],
        ], [
            'name.required' => 'Room name is required.',
            'game_type.in' => 'Game type must be either poker or blot.',
            'stake.min' => 'Stake must be a positive number.',
            'max_players.min' => 'Minimum 2 players required.',
            'max_players.max' => 'Maximum 10 players allowed.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        GameRoom::create($validator->validated());

        return redirect()->route('admin.gamerooms.index')
            ->with('success', 'Game room created successfully.');
    }

    /**
     * Show game room edit form
     */
    public function edit(GameRoom $room): View
    {
        $roomStats = $this->getGameRoomStats($room);

        return view('admin.gamerooms.edit', [
            'room' => $room,
            'roomStats' => $roomStats
        ]);
    }

    /**
     * Update game room
     */
    public function update(Request $request, GameRoom $room): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'game_type' => ['required', 'in:poker,blot'],
            'status' => ['required', 'in:waiting,disabled,in_progress,completed'],
            'stake' => ['required', 'numeric', 'min:0'],
            'max_players' => ['required', 'integer', 'min:2', 'max:10'],
        ], [
            'name.required' => 'Room name is required.',
            'game_type.in' => 'Game type must be either poker or blot.',
            'stake.min' => 'Stake must be a positive number.',
            'max_players.min' => 'Minimum 2 players required.',
            'max_players.max' => 'Maximum 10 players allowed.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $room->update($validator->validated());

        return redirect()->route('admin.gamerooms.index')
            ->with('success', 'Game room updated successfully.');
    }

    /**
     * Delete game room
     */
    public function destroy(GameRoom $room): RedirectResponse
    {
        try {
            // Check if room has players
            if ($room->players()->exists()) {
                // Instead of throwing AdminException, we'll return a redirect with error
                return redirect()->back()
                    ->with('error', 'Cannot delete game room with active players.');
            }
            
            $room->delete();

            return redirect()->route('admin.gamerooms.index')
                ->with('success', 'Game room deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show game room details
     */
    public function show(GameRoom $room): View
    {
        $room->load(['players' => function ($query) {
            $query->select('users.id', 'users.username', 'users.avatar');
        }]);

        $roomStats = $this->getGameRoomStats($room);

        return view('admin.gamerooms.show', [
            'room' => $room,
            'roomStats' => $roomStats
        ]);
    }

    /**
     * Get game room statistics
     */
    private function getGameRoomStats(GameRoom $room): array
    {
        return [
            'total_players' => $room->players()->count(),
            'total_games_played' => 0, // TODO: Add game history tracking
            'total_revenue' => $room->players()->count() * $room->stake,
            'average_players' => $room->players()->count(), // TODO: Add historical data
        ];
    }
    
    /**
     * Start game room
     */
    public function start(GameRoom $room): RedirectResponse
    {
        try {
            if ($room->status !== GameRoom::STATUS_WAITING) {
                return redirect()->back()
                    ->with('error', 'Game room is not in waiting status.');
            }

            $room->update([
                'status' => GameRoom::STATUS_IN_PROGRESS,
            ]);

            return redirect()->back()
                ->with('success', 'Game room started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Complete game room
     */
    public function complete(GameRoom $room): RedirectResponse
    {
        try {
            if ($room->status !== GameRoom::STATUS_IN_PROGRESS) {
                return redirect()->back()
                    ->with('error', 'Game room is not in progress.');
            }

            $room->update([
                'status' => GameRoom::STATUS_COMPLETED,
            ]);

            return redirect()->back()
                ->with('success', 'Game room completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel game room
     */
    public function cancel(GameRoom $room): RedirectResponse
    {
        try {
            if (!in_array($room->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS])) {
                return redirect()->back()
                    ->with('error', 'Game room cannot be cancelled in current status.');
            }

            $room->update([
                'status' => GameRoom::STATUS_COMPLETED,
            ]);

            // TODO: Implement refund logic for players

            return redirect()->back()
                ->with('success', 'Game room cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Enable game room
     */
    public function enable(GameRoom $room): RedirectResponse
    {
        try {
            if ($room->status !== GameRoom::STATUS_DISABLED) {
                return redirect()->back()
                    ->with('error', 'Game room is not disabled.');
            }

            $room->update([
                'status' => GameRoom::STATUS_WAITING,
            ]);

            return redirect()->back()
                ->with('success', 'Game room enabled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}