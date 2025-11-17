<?php

namespace App\Http\Controllers\Api;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TournamentRegistrationRequest;
use App\Services\TournamentService;
use App\Traits\ApiResponser;
use Exception;

class TournamentController extends Controller
{
    use ApiResponser;

    /**
     * @var TournamentService
     */
    protected $tournamentService;

    /**
     * TournamentController constructor.
     *
     * @param TournamentService $tournamentService
     */
    public function __construct(TournamentService $tournamentService)
    {
        $this->tournamentService = $tournamentService;
    }

    /**
     * Get list of available tournaments
     */
    public function index(): JsonResponse
    {
        try {
            $tournamentsData = $this->tournamentService->getAvailableTournaments();
            return $this->successResponse($tournamentsData, 'Tournaments fetched successfully');
        } catch (Exception $e) {
            Log::error('Error fetching tournaments', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse('An error occurred while fetching tournaments.', 500);
        }
    }

    /**
     * Register user for tournament
     */
    public function register(TournamentRegistrationRequest $request, Tournament $tournament): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->tournamentService->registerForTournament($user, $tournament);

            return $this->successResponse($result, 'Successfully registered for tournament');
        } catch (Exception $e) {
            Log::warning('Tournament exception', [
                'user_id' => $request->user()->id,
                'tournament_id' => $tournament->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $statusCode = 500;
            if (in_array($e->getMessage(), [
                'This tournament is not available for registration.',
                'You are already registered for this tournament.',
                'This tournament is full.',
                'Insufficient balance to register for this tournament.'
            ])) {
                $statusCode = 400;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Get tournament details
     */
    public function show(Tournament $tournament): JsonResponse
    {
        try {
            $tournamentData = $this->tournamentService->getTournamentDetails($tournament);
            return $this->successResponse($tournamentData, 'Tournament details fetched successfully');
        } catch (Exception $e) {
            Log::error('Error fetching tournament details', [
                'tournament_id' => $tournament->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse('An error occurred while fetching tournament details.', 500);
        }
    }
}