<?php

namespace App\Services\Poker\Engine;

use App\Models\GameRoom;
use App\Models\GameState;
use App\DTOs\GameStateData;
use App\Services\WalletService;
use App\Services\TransactionService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Events\PotDistributed;
use App\Jobs\StartNewGameRound;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DistributePotService
{
    protected $gameRoomPlayerRepository;
    protected $walletService;
    protected $transactionService;

    public function __construct(
        GameRoomPlayerRepository $gameRoomPlayerRepository,
        WalletService $walletService,
        TransactionService $transactionService
    ) {
        $this->gameRoomPlayerRepository = $gameRoomPlayerRepository;
        $this->walletService = $walletService;
        $this->transactionService = $transactionService;
    }

    /**
     * Distribute the pot to winners and start a new hand
     *
     * @param GameRoom $room
     * @param array $winners Array of winner data with player_id and amount
     * @return void
     */
    public function execute(GameRoom $room, array $winners): void
    {
        Log::info('[POKER ENGINE] Distributing pot for room: ' . $room->id);

        try {
            DB::transaction(function () use ($room, $winners) {
                // Process side pots if needed
                $this->processSidePots($room, $winners);
                
                // Distribute main pot to winners
                $this->distributeToWinners($room, $winners);
                
                // Broadcast pot distribution
                broadcast(new PotDistributed($room->id, $winners));
                
                // Schedule new hand to start in 5 seconds
                StartNewGameRound::dispatch($room)->delay(now()->addSeconds(5));
            });
            
            Log::info('[POKER ENGINE] Pot distributed successfully for room: ' . $room->id);
        } catch (\Exception $e) {
            Log::error('[POKER ENGINE] Failed to distribute pot for room: ' . $room->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Process side pots (for all-in situations)
     *
     * @param GameRoom $room
     * @param array $winners
     * @return void
     */
    private function processSidePots(GameRoom $room, array $winners): void
    {
        // In a full implementation, we would:
        // 1. Identify all-in players and their stack sizes
        // 2. Create side pots for each all-in level
        // 3. Determine eligible winners for each side pot
        // 4. Distribute each side pot separately
        
        // For now, we'll assume a simple pot split
        Log::info('[POKER ENGINE] Processing side pots for room: ' . $room->id);
    }

    /**
     * Distribute pot to winners
     *
     * @param GameRoom $room
     * @param array $winners
     * @return void
     */
    private function distributeToWinners(GameRoom $room, array $winners): void
    {
        foreach ($winners as $winner) {
            $playerId = $winner['player_id'];
            $amount = $winner['amount'];
            
            // Award amount to winner
            $this->gameRoomPlayerRepository->updatePlayerStack($room->id, $playerId, $amount);
            
            // Create win transaction
            $winnerUser = $this->gameRoomPlayerRepository->getPlayerUser($room, $playerId);
            if ($winnerUser) {
                $this->transactionService->createTransaction([
                    'wallet_id' => $winnerUser->wallet->id,
                    'type' => 'game_win',
                    'amount' => $amount,
                    'description' => "Poker pot win"
                ]);
            }
        }
    }
}