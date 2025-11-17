<?php

namespace App\Services\Poker\Engine;

use App\Models\GameRoom;
use App\Models\GameState;
use App\DTOs\GameStateData;
use App\Services\Poker\HandEvaluator;
use App\Services\WalletService;
use App\Services\TransactionService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Events\ShowdownOccurred;
use App\Events\PotDistributed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessShowdownService
{
    protected $gameRoomPlayerRepository;
    protected $walletService;
    protected $transactionService;
    protected $handEvaluator;

    public function __construct(
        GameRoomPlayerRepository $gameRoomPlayerRepository,
        WalletService $walletService,
        TransactionService $transactionService
    ) {
        $this->gameRoomPlayerRepository = $gameRoomPlayerRepository;
        $this->walletService = $walletService;
        $this->transactionService = $transactionService;
        $this->handEvaluator = new HandEvaluator();
    }

    /**
     * Process the showdown and determine winners
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    public function execute(GameRoom $room, GameStateData $gameState): void
    {
        Log::info('[POKER ENGINE] Processing showdown for room: ' . $room->id);

        try {
            DB::transaction(function () use ($room, $gameState) {
                // Get active players
                $activePlayers = $this->getActivePlayers($gameState);
                
                if (count($activePlayers) <= 1) {
                    // Only one player left, they win
                    $winnerId = array_key_first($activePlayers);
                    $this->awardPotToPlayer($room, $gameState, $winnerId);
                    return;
                }
                
                // Evaluate hands for all active players
                $playerHands = $this->evaluatePlayerHands($gameState, $activePlayers);
                
                // Determine winners
                $winners = $this->determineWinners($playerHands);
                
                // Distribute pot
                $this->distributePot($room, $gameState, $winners);
                
                // Broadcast showdown results
                $this->broadcastShowdownResults($room, $gameState, $playerHands, $winners);
                
                // Clear game state
                GameState::deleteState($room->id);
            });
            
            Log::info('[POKER ENGINE] Showdown processed successfully for room: ' . $room->id);
        } catch (\Exception $e) {
            Log::error('[POKER ENGINE] Failed to process showdown for room: ' . $room->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get active players (not folded)
     *
     * @param GameStateData $gameState
     * @return array
     */
    private function getActivePlayers(GameStateData $gameState): array
    {
        return array_filter($gameState->playersState, function ($player) {
            return !$player['has_folded'];
        });
    }

    /**
     * Evaluate hands for all active players
     *
     * @param GameStateData $gameState
     * @param array $activePlayers
     * @return array
     */
    private function evaluatePlayerHands(GameStateData $gameState, array $activePlayers): array
    {
        $communityCards = $gameState->communityCards;
        $playerHands = [];
        
        foreach ($activePlayers as $playerId => $playerState) {
            // Combine player's pocket cards with community cards
            $pocketCards = $playerState['cards'];
            $allCards = array_merge($pocketCards, $communityCards);
            
            // Evaluate hand
            $handResult = $this->handEvaluator->evaluateHand($allCards);
            
            $playerHands[$playerId] = [
                'player' => $playerState,
                'cards' => $allCards,
                'hand_result' => $handResult
            ];
        }
        
        return $playerHands;
    }

    /**
     * Determine winners based on hand rankings
     *
     * @param array $playerHands
     * @return array
     */
    private function determineWinners(array $playerHands): array
    {
        // Sort players by hand rank (highest first)
        uasort($playerHands, function ($a, $b) {
            return $b['hand_result']['rank'] <=> $a['hand_result']['rank'];
        });
        
        // Get the best hand rank
        $bestRank = reset($playerHands)['hand_result']['rank'];
        
        // Find all players with the best rank
        $winners = [];
        foreach ($playerHands as $playerId => $handData) {
            if ($handData['hand_result']['rank'] === $bestRank) {
                $winners[$playerId] = $handData;
            }
        }
        
        return $winners;
    }

    /**
     * Distribute pot to winners
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param array $winners
     * @return void
     */
    private function distributePot(GameRoom $room, GameStateData $gameState, array $winners): void
    {
        $pot = $gameState->pot;
        $winnerCount = count($winners);
        
        if ($winnerCount === 0) {
            Log::warning('[POKER ENGINE] No winners found in showdown for room: ' . $room->id);
            return;
        }
        
        // Split pot equally among winners
        $splitAmount = $pot / $winnerCount;
        
        $distributionData = [];
        
        foreach ($winners as $winnerId => $winnerData) {
            // Award split amount to winner
            $this->gameRoomPlayerRepository->updatePlayerStack($room->id, $winnerId, $splitAmount);
            
            // Create win transaction
            $winnerUser = $this->gameRoomPlayerRepository->getPlayerUser($room, $winnerId);
            if ($winnerUser) {
                $this->transactionService->createTransaction(
                    $winnerUser->wallet,
                    'game_win',
                    $splitAmount,
                    "Poker pot win (split)"
                );
            }
            
            $distributionData[] = [
                'player_id' => $winnerId,
                'username' => $winnerData['player']['username'],
                'amount' => $splitAmount,
                'is_split' => $winnerCount > 1
            ];
        }
        
        // Broadcast pot distribution
        broadcast(new PotDistributed($room->id, $distributionData));
    }

    /**
     * Broadcast showdown results
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param array $playerHands
     * @param array $winners
     * @return void
     */
    private function broadcastShowdownResults(GameRoom $room, GameStateData $gameState, array $playerHands, array $winners): void
    {
        $showdownData = [
            'community_cards' => $gameState->communityCards,
            'players' => [],
            'winners' => []
        ];
        
        // Add all player hands
        foreach ($playerHands as $playerId => $handData) {
            $showdownData['players'][] = [
                'player_id' => $playerId,
                'username' => $handData['player']['username'],
                'cards' => array_map(fn($card) => $card->toArray(), $handData['player']['cards']),
                'hand_result' => $handData['hand_result']
            ];
        }
        
        // Add winners
        foreach ($winners as $winnerId => $winnerData) {
            $showdownData['winners'][] = [
                'player_id' => $winnerId,
                'username' => $winnerData['player']['username']
            ];
        }
        
        broadcast(new ShowdownOccurred($room->id, $showdownData));
    }

    /**
     * Award pot to a single player
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param int $winnerId
     * @return void
     */
    private function awardPotToPlayer(GameRoom $room, GameStateData $gameState, int $winnerId): void
    {
        $winner = $gameState->playersState[$winnerId];
        $pot = $gameState->pot;
        
        // Award pot to winner
        $this->gameRoomPlayerRepository->updatePlayerStack($room->id, $winnerId, $pot);
        
        // Create win transaction
        $winnerUser = $this->gameRoomPlayerRepository->getPlayerUser($room, $winnerId);
        if ($winnerUser) {
            $this->transactionService->createTransaction(
                $winnerUser->wallet,
                'game_win',
                $pot,
                "Poker pot win"
            );
        }
        
        // Broadcast pot distribution
        broadcast(new PotDistributed($room->id, [
            [
                'player_id' => $winnerId,
                'username' => $winner['username'],
                'amount' => $pot,
                'is_split' => false
            ]
        ]));
    }
}