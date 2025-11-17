<?php

namespace App\Repositories\Eloquent;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Model;

class TournamentRepository extends \App\Repositories\BaseRepository
{
    /**
     * TournamentRepository constructor.
     *
     * @param Tournament $model
     */
    public function __construct(Tournament $model)
    {
        parent::__construct($model);
    }

    /**
     * Get available tournaments
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTournaments()
    {
        return $this->model->available()->get();
    }

    /**
     * Get upcoming tournaments
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingTournaments()
    {
        return $this->model->upcoming()->get();
    }

    /**
     * Get tournaments by game type
     *
     * @param string $gameType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByGameType(string $gameType)
    {
        return $this->model->ofType($gameType)->get();
    }
}