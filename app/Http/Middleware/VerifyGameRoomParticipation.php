<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GameRoom;

class VerifyGameRoomParticipation
{
    /**
     * Handle an incoming request to verify game room participation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the room parameter from the route
        $roomParam = $request->route('room');
        
        // If we have a room parameter
        if ($roomParam) {
            // Ensure we have a GameRoom model instance
            if ($roomParam instanceof GameRoom) {
                // Already resolved by implicit route binding
                $room = $roomParam;
            } else {
                // It's likely an ID, so find the room
                $room = GameRoom::find($roomParam);
            }
            
            // If room not found, return error
            if (!$room) {
                return response()->json(['error' => 'Room not found'], 404);
            }
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            $isParticipant = $room->players()
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$isParticipant) {
                return response()->json(['error' => 'Not a participant'], 403);
            }
        }
        
        return $next($request);
    }
}