<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tournament;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the tournament.
     */
    public function view(User $user, Tournament $tournament): bool
    {
        // Anyone can view tournaments
        return true;
    }

    /**
     * Determine whether the user can register for the tournament.
     */
    public function register(User $user, Tournament $tournament): bool
    {
        // Check if the tournament is available for registration
        return $tournament->status === 'open';
    }

    /**
     * Determine whether the user can update the tournament.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        // Only admins can update tournaments
        return false;
    }
}