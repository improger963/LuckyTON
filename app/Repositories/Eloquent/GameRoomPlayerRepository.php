<?php

namespace App\Repositories\Eloquent;

use App\Models\GameRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Collection;

class GameRoomPlayerRepository
{
    /**
     * Find player/spectator record in room
     *
     * @param GameRoom $room
     * @param User $user
     * @return stdClass|null
     */
    public function findPlayer(GameRoom $room, User $user)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Add user as spectator to room
     *
     * @param GameRoom $room
     * @param User $user
     * @return bool
     */
    public function addSpectator(GameRoom $room, User $user)
    {
        return DB::table('game_room_players')->insert([
            'game_room_id' => $room->id,
            'user_id' => $user->id,
            'role' => 'spectator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Update user to player role
     *
     * @param GameRoom $room
     * @param User $user
     * @param int $seat
     * @param float $stack
     * @return int
     */
    public function updateToPlayer(GameRoom $room, User $user, int $seat, float $stack)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->update([
                'role' => 'player',
                'seat' => $seat,
                'stack' => $stack,
                'updated_at' => now(),
            ]);
    }

    /**
     * Update user to spectator role
     *
     * @param GameRoom $room
     * @param User $user
     * @return int
     */
    public function updateToSpectator(GameRoom $room, User $user)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->update([
                'role' => 'spectator',
                'seat' => null,
                'stack' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Remove user from room
     *
     * @param GameRoom $room
     * @param User $user
     * @return int
     */
    public function remove(GameRoom $room, User $user)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Get players in room
     *
     * @param GameRoom $room
     * @return Collection
     */
    public function getPlayers(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('role', 'player')
            ->get();
    }

    /**
     * Get players with user details in room
     *
     * @param GameRoom $room
     * @return Collection
     */
    public function getPlayersWithUsers(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->join('users', 'game_room_players.user_id', '=', 'users.id')
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->where('game_room_players.game_room_id', $room->id)
            ->where('game_room_players.role', 'player')
            ->select('game_room_players.*', 'users.id as user_id', 'users.username', 'wallets.balance as wallet_balance')
            ->get();
    }

    /**
     * Get spectators in room
     *
     * @param GameRoom $room
     * @return Collection
     */
    public function getSpectators(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('role', 'spectator')
            ->get();
    }

    /**
     * Get all participants in room
     *
     * @param GameRoom $room
     * @return Collection
     */
    public function getParticipants(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->get();
    }

    /**
     * Check if seat is occupied
     *
     * @param GameRoom $room
     * @param int $seat
     * @return bool
     */
    public function isSeatOccupied(GameRoom $room, int $seat)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('seat', $seat)
            ->where('role', 'player')
            ->exists();
    }

    /**
     * Get available seats
     *
     * @param GameRoom $room
     * @return array
     */
    public function getAvailableSeats(GameRoom $room)
    {
        $occupiedSeats = DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('role', 'player')
            ->pluck('seat')
            ->toArray();

        $availableSeats = [];
        for ($i = 1; $i <= $room->max_players; $i++) {
            if (!in_array($i, $occupiedSeats)) {
                $availableSeats[] = $i;
            }
        }

        return $availableSeats;
    }

    /**
     * Get count of players in room
     *
     * @param GameRoom $room
     * @return int
     */
    public function getPlayerCount(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('role', 'player')
            ->count();
    }

    /**
     * Get occupied seats
     *
     * @param GameRoom $room
     * @return array
     */
    public function getOccupiedSeats(GameRoom $room)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $room->id)
            ->where('role', 'player')
            ->pluck('seat')
            ->toArray();
    }

    /**
     * Update player stack by adding/subtracting amount
     *
     * @param int $roomId
     * @param int $playerId
     * @param float $amount
     * @return int
     */
    public function updatePlayerStack(int $roomId, int $playerId, float $amount)
    {
        return DB::table('game_room_players')
            ->where('game_room_id', $roomId)
            ->where('user_id', $playerId)
            ->increment('stack', $amount);
    }

    /**
     * Get user associated with a player in a room
     *
     * @param GameRoom $room
     * @param int $playerId
     * @return User|null
     */
    public function getPlayerUser(GameRoom $room, int $playerId)
    {
        $player = DB::table('game_room_players')
            ->join('users', 'game_room_players.user_id', '=', 'users.id')
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->where('game_room_players.game_room_id', $room->id)
            ->where('game_room_players.user_id', $playerId)
            ->select('users.*', 'wallets.balance as wallet_balance')
            ->first();

        if ($player) {
            // Create a User model instance from the stdClass object
            $user = new User((array) $player);
            $user->id = $player->id;
            return $user;
        }

        return null;
    }
}