<?php

namespace App\Repositories\Eloquent;

use App\Models\GameRoom;
use Illuminate\Database\Eloquent\Model;

class GameRoomRepository extends \App\Repositories\BaseRepository
{
    /**
     * GameRoomRepository constructor.
     *
     * @param GameRoom $model
     */
    public function __construct(GameRoom $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a room by ID
     *
     * @param int $id
     * @return GameRoom|null
     */
    public function find(int $id): ?GameRoom
    {
        return $this->model->find($id);
    }

    /**
     * Get active rooms for lobby
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveRooms()
    {
        // Simplified query to show all active rooms regardless of player count or game status
        // Only filter by admin-set status (active or waiting), exclude maintenance rooms
        return $this->model->whereIn('status', ['waiting', 'in_progress'])
            ->withCount(['players' => function ($query) {
                $query->where('role', 'player');
            }])
            ->orderBy('players_count', 'desc') // Sort by popularity
            ->get();
    }

    /**
     * Get available rooms with player count
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRoomsWithPlayerCount()
    {
        // Simplified query to show all active rooms regardless of player count or game status
        // Only filter by admin-set status (active or waiting), exclude maintenance rooms
        return $this->model->whereIn('status', ['waiting', 'in_progress'])
            ->withCount(['players' => function ($query) {
                $query->where('role', 'player');
            }])
            ->orderBy('players_count', 'desc') // Sort by popularity
            ->get();
    }

    /**
     * Find room by game type
     *
     * @param string $gameType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByGameType(string $gameType)
    {
        return $this->model->ofType($gameType)->get();
    }

    /**
     * Get room with players
     *
     * @param int $id
     * @return GameRoom|null
     */
    public function findWithPlayers(int $id): ?GameRoom
    {
        return $this->model->with('players')->find($id);
    }

    /**
     * Get room with players and their roles
     *
     * @param int $id
     * @return GameRoom|null
     */
    public function findWithPlayersAndRoles(int $id): ?GameRoom
    {
        return $this->model->with(['players' => function ($query) {
            $query->withPivot(['role', 'stack', 'seat']);
        }])->find($id);
    }
}