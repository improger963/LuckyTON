<?php

namespace App\Services;

use App\Repositories\Eloquent\TournamentRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Models\User;
use App\Models\Tournament;
use Illuminate\Support\Facades\Log;
use Exception;

class TournamentService
{
    /**
     * @var TournamentRepository
     */
    protected $tournamentRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * TournamentService constructor.
     *
     * @param TournamentRepository $tournamentRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        TournamentRepository $tournamentRepository,
        UserRepository $userRepository
    ) {
        $this->tournamentRepository = $tournamentRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get list of available tournaments
     *
     * @return array
     */
    public function getAvailableTournaments(): array
    {
        // Use eager loading and query scopes to optimize
        $tournaments = $this->tournamentRepository->getAvailableTournaments()
            ->withCount('players')
            ->orderBy('starts_at', 'asc')
            ->get();

        $formattedTournaments = $tournaments->map(function ($tournament) {
            return $this->formatTournamentData($tournament);
        })->toArray();

        return $formattedTournaments;
    }

    /**
     * Register user for tournament
     *
     * @param User $user
     * @param Tournament $tournament
     * @return array
     * @throws Exception
     */
    public function registerForTournament(User $user, Tournament $tournament): array
    {
        // Check if tournament is available for registration
        if (!$tournament->isJoinable()) {
            throw new Exception('This tournament is not available for registration.');
        }

        // Check if user is already registered
        if ($tournament->players()->where('user_id', $user->id)->exists()) {
            throw new Exception('You are already registered for this tournament.');
        }

        // Check if tournament is full
        if ($tournament->players()->count() >= $tournament->max_players) {
            throw new Exception('This tournament is full.');
        }

        // Check user balance
        if ($user->wallet->balance < $tournament->buy_in) {
            throw new Exception('Insufficient balance to register for this tournament.');
        }

        // Register user for tournament
        $tournament->players()->attach($user->id, [
            'registered_at' => now(),
        ]);

        // Deduct buy-in from user's wallet
        $user->wallet->decrement('balance', $tournament->buy_in);

        // Reload tournament with players
        $tournament->load('players');

        // Transform tournament data
        $currentPlayers = $tournament->players->count();
        $timeUntilStart = 'Started';
        if ($tournament->starts_at->isFuture()) {
            $interval = now()->diff($tournament->starts_at);

            $parts = [];
            if ($interval->d > 0) $parts[] = $interval->d . 'д';
            if ($interval->h > 0) $parts[] = $interval->h . 'ч';
            if ($interval->d == 0 && $interval->h == 0 && $interval->i > 0) $parts[] = $interval->i . 'м';

            if ($interval->d > 1) {
                $timeUntilStart = $parts[0] . ' ' . ($parts[1] ?? '');
            } else {
                $timeUntilStart = implode(' ', $parts);
            }
        }

        $transformedTournament = [
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'game_type' => $tournament->game_type,
            'prize_pool' => number_format($tournament->prize_pool, 0),
            'buy_in' => number_format($tournament->buy_in, 0),
            'currency' => 'USDT',
            'max_players' => $tournament->max_players,
            'current_players' => (int) $currentPlayers,
            'available_spots' => max(0, $tournament->max_players - (int) $currentPlayers),
            'players_string' => $currentPlayers . '/' . $tournament->max_players,
            'time_until_start' => $timeUntilStart,
            'registration_opens_at' => $tournament->registration_opens_at?->toISOString(),
            'starts_at_iso' => $tournament->starts_at->toISOString(),
            'starts_at_human' => $tournament->starts_at->diffForHumans(),
            'status' => $tournament->status,
            'is_joinable' => $tournament->isJoinable(),
            'is_full' => $tournament->isFull(),
            'created_at' => $tournament->created_at?->toISOString(),
        ];

        return [
            'message' => 'You have successfully registered for the tournament.',
            'tournament' => $transformedTournament
        ];
    }

    /**
     * Get tournament details
     *
     * @param Tournament $tournament
     * @return array
     */
    public function getTournamentDetails(Tournament $tournament): array
    {
        $tournament->load(['players' => function ($query) {
            $query->select('users.id', 'users.username', 'users.avatar');
        }]);

        // Transform tournament data
        $currentPlayers = $tournament->players->count();
        $timeUntilStart = 'Started';
        if ($tournament->starts_at->isFuture()) {
            $interval = now()->diff($tournament->starts_at);

            $parts = [];
            if ($interval->d > 0) $parts[] = $interval->d . 'д';
            if ($interval->h > 0) $parts[] = $interval->h . 'ч';
            if ($interval->d == 0 && $interval->h == 0 && $interval->i > 0) $parts[] = $interval->i . 'м';

            if ($interval->d > 1) {
                $timeUntilStart = $parts[0] . ' ' . ($parts[1] ?? '');
            } else {
                $timeUntilStart = implode(' ', $parts);
            }
        }

        $transformedTournament = [
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'game_type' => $tournament->game_type,
            'prize_pool' => number_format($tournament->prize_pool, 0),
            'buy_in' => number_format($tournament->buy_in, 0),
            'currency' => 'USDT',
            'max_players' => $tournament->max_players,
            'current_players' => (int) $currentPlayers,
            'available_spots' => max(0, $tournament->max_players - (int) $currentPlayers),
            'players_string' => $currentPlayers . '/' . $tournament->max_players,
            'time_until_start' => $timeUntilStart,
            'registration_opens_at' => $tournament->registration_opens_at?->toISOString(),
            'starts_at_iso' => $tournament->starts_at->toISOString(),
            'starts_at_human' => $tournament->starts_at->diffForHumans(),
            'status' => $tournament->status,
            'is_joinable' => $tournament->isJoinable(),
            'is_full' => $tournament->isFull(),
            'created_at' => $tournament->created_at?->toISOString(),
        ];

        return $transformedTournament;
    }

    /**
     * Format tournament data for response
     *
     * @param Tournament $tournament
     * @return array
     */
    private function formatTournamentData(Tournament $tournament): array
    {
        // Transform tournament data
        $currentPlayers = $tournament->players->count();
        $timeUntilStart = 'Started';
        if ($tournament->starts_at->isFuture()) {
            $interval = now()->diff($tournament->starts_at);

            $parts = [];
            if ($interval->d > 0) $parts[] = $interval->d . 'д';
            if ($interval->h > 0) $parts[] = $interval->h . 'ч';
            if ($interval->d == 0 && $interval->h == 0 && $interval->i > 0) $parts[] = $interval->i . 'м';

            if ($interval->d > 1) {
                $timeUntilStart = $parts[0] . ' ' . ($parts[1] ?? '');
            } else {
                $timeUntilStart = implode(' ', $parts);
            }
        }

        return [
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'game_type' => $tournament->game_type,
            'prize_pool' => number_format($tournament->prize_pool, 0),
            'buy_in' => number_format($tournament->buy_in, 0),
            'currency' => 'USDT',
            'max_players' => $tournament->max_players,
            'current_players' => (int) $currentPlayers,
            'available_spots' => max(0, $tournament->max_players - (int) $currentPlayers),
            'players_string' => $currentPlayers . '/' . $tournament->max_players,
            'time_until_start' => $timeUntilStart,
            'registration_opens_at' => $tournament->registration_opens_at?->toISOString(),
            'starts_at_iso' => $tournament->starts_at->toISOString(),
            'starts_at_human' => $tournament->starts_at->diffForHumans(),
            'status' => $tournament->status,
            'is_joinable' => $tournament->isJoinable(),
            'is_full' => $tournament->isFull(),
            'created_at' => $tournament->created_at?->toISOString(),
        ];
    }
}