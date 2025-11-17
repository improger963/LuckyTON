<?php

namespace App\Jobs;

use App\Models\GameRoom;
use App\Services\GameService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Events\GameStateUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StartNewGameRound implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GameRoom $room
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GameService $gameService, GameRoomPlayerRepository $gameRoomPlayerRepository): void
    {
        Log::info('[QUEUE JOB STARTED] Starting new game round for room: ' . $this->room->id);

        try {
            // Check if there are at least 2 players in the room
            $playerCount = $gameRoomPlayerRepository->getPlayerCount($this->room);
            
            if ($playerCount >= 2) {
                // Start a new poker game using the existing GameService
                $gameService->startPokerGame($this->room);
                Log::info('[QUEUE JOB SUCCESS] New game round started successfully for room: ' . $this->room->id);
            } else {
                // Not enough players, change room status back to waiting
                $this->room->status = GameRoom::STATUS_WAITING;
                $this->room->save();
                
                // Broadcast game state update
                broadcast(new GameStateUpdated($this->room->id, [
                    'status' => 'waiting',
                    'players_count' => $playerCount,
                    // Add other relevant data for UI reset
                ]));
                
                Log::info('[QUEUE JOB CANCELLED] Not enough players to start new game round for room: ' . $this->room->id);
            }
        } catch (\Exception $e) {
            Log::error('[QUEUE JOB FAILED] Failed to start new game round for room: ' . $this->room->id, [
                'error' => $e->getMessage()
            ]);
            // Can "fail" the task so it goes to failed_jobs
            $this->fail($e);
        }
    }
}