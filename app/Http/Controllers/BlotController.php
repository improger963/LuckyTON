<?php

namespace App\Http\Controllers;

use App\Models\GameRoom;
use App\Models\User;
use App\Services\Game\BlotGameService;
use App\Events\Blot\CombinationAnnounced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlotController extends Controller
{
    /**
     * Handle trump selection
     *
     * @param Request $request
     * @param GameRoom $room
     * @param BlotGameService $blotGameService
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectTrump(Request $request, GameRoom $room, BlotGameService $blotGameService)
    {
        $request->validate([
            'agreed' => 'required|boolean',
            'new_trump' => 'nullable|string|in:hearts,diamonds,clubs,spades',
        ]);

        /** @var User $user */
        $user = Auth::user();

        try {
            $blotGameService->handleTrumpSelection(
                $user,
                $room,
                $request->boolean('agreed'),
                $request->string('new_trump')->toString()
            );

            return response()->json(['message' => 'Trump selection processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle announcing combinations
     *
     * @param Request $request
     * @param GameRoom $room
     * @param BlotGameService $blotGameService
     * @return \Illuminate\Http\JsonResponse
     */
    public function announceCombination(Request $request, GameRoom $room, BlotGameService $blotGameService)
    {
        $request->validate([
            'combination' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();

        try {
            // In a full implementation, you would validate the combination and store it
            // For now, we'll just broadcast the announcement
            
            broadcast(new CombinationAnnounced($room->id, $user->id, $request->input('combination')))->toOthers();
            
            return response()->json(['message' => 'Combination announced successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}