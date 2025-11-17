<?php

namespace App\Repositories\Eloquent;

use App\Models\GameState;
use Illuminate\Database\Eloquent\Model;

class GameStateRepository extends \App\Repositories\BaseRepository
{
    /**
     * GameStateRepository constructor.
     *
     * @param GameState $model
     */
    public function __construct(GameState $model)
    {
        parent::__construct($model);
    }

    /**
     * Find game state by room ID
     *
     * @param int $roomId
     * @return GameState|null
     */
    public function findByRoomId(int $roomId): ?GameState
    {
        return $this->model->where('room_id', $roomId)->first();
    }

    /**
     * Get game states with rooms
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStatesWithRooms()
    {
        return $this->model->with('room')->get();
    }
}