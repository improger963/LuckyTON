<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GameRoom;
use Illuminate\Auth\Access\HandlesAuthorization;

class GameRoomPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the game room.
     */
    public function view(User $user, GameRoom $gameRoom): bool
    {
        // Anyone can view game rooms
        return true;
    }

    /**
     * Determine whether the user can join the game room.
     */
    public function join(User $user, GameRoom $gameRoom): bool
    {
        // Check if the room is available for joining
        return $gameRoom->status === 'active';
    }

    /**
     * Determine whether the user can update the game room.
     */
    public function update(User $user, GameRoom $gameRoom): bool
    {
        // Only admins can update game rooms
        return false;
    }
}