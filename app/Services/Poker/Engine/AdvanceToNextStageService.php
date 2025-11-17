<?php

namespace App\Services\Poker\Engine;

use App\Models\GameRoom;
use App\Models\GameState;
use App\DTOs\GameStateData;
use App\Services\Game\DeckService;
use App\Services\WalletService;
use App\Services\TransactionService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Events\GameStateUpdated;
use App\Events\CommunityCardsDealt;
use App\Events\PlayerTurn;
use App\Events\PotDistributed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdvanceToNextStageService
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
     * Advance to the next stage of the game
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    public function execute(GameRoom $room, GameStateData $gameState): void
    {
        Log::info('[POKER ENGINE] Advancing to next stage for room: ' . $room->id, [
            'current_phase' => $gameState->phase
        ]);

        try {
            DB::transaction(function () use ($room, $gameState) {
                // Check if only one player remains
                $activePlayers = $this->getActivePlayers($gameState);
                
                if (count($activePlayers) <= 1) {
                    // Only one player left, they win
                    $winnerId = array_key_first($activePlayers);
                    $this->awardPotToPlayer($room, $gameState, $winnerId);
                    return;
                }
                
                // Determine next phase
                switch ($gameState->phase) {
                    case 'preflop':
                        $this->advanceToFlop($room, $gameState);
                        break;
                        
                    case 'flop':
                        $this->advanceToTurn($room, $gameState);
                        break;
                        
                    case 'turn':
                        $this->advanceToRiver($room, $gameState);
                        break;
                        
                    case 'river':
                        // Process showdown
                        $showdownService = new ProcessShowdownService(
                            $this->gameRoomPlayerRepository,
                            $this->walletService,
                            $this->transactionService
                        );
                        $showdownService->execute($room, $gameState);
                        break;
                        
                    default:
                        throw new \Exception('Invalid game phase: ' . $gameState->phase);
                }
            });
            
            Log::info('[POKER ENGINE] Advanced to next stage successfully for room: ' . $room->id);
        } catch (\Exception $e) {
            Log::error('[POKER ENGINE] Failed to advance to next stage for room: ' . $room->id, [
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
     * Advance to flop stage
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    private function advanceToFlop(GameRoom $room, GameStateData $gameState): void
    {
        // Burn one card
        $deck = new DeckService();
        $deck->shuffle(); // We'll need to implement deck persistence in a real implementation
        
        // Draw three community cards
        $communityCards = $deck->drawMultiple(3)->toArray();
        
        // Update game state
        $gameState->phase = 'flop';
        $gameState->communityCards = array_map(fn($card) => $card->toArray(), $communityCards);
        
        // Reset betting round
        $this->resetBettingRound($gameState);
        
        // Save state
        GameState::saveState($room->id, $gameState->toArray());
        
        // Broadcast community cards
        broadcast(new CommunityCardsDealt(
            array_map(fn($card) => $card->toArray(), $communityCards),
            $room->id
        ));
        
        // Broadcast updated state
        $publicState = $gameState->toArray();
        foreach ($publicState['players_state'] as &$playerState) {
            unset($playerState['cards']);
        }
        broadcast(new GameStateUpdated($room->id, $publicState));
        
        // Notify first player of their turn
        $this->notifyFirstPlayerOfTurn($room, $gameState);
    }

    /**
     * Advance to turn stage
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    private function advanceToTurn(GameRoom $room, GameStateData $gameState): void
    {
        // Burn one card
        $deck = new DeckService();
        $deck->shuffle(); // We'll need to implement deck persistence in a real implementation
        
        // Draw one community card
        $communityCard = $deck->draw();
        
        // Update game state
        $gameState->phase = 'turn';
        $gameState->communityCards[] = $communityCard->toArray();
        
        // Reset betting round
        $this->resetBettingRound($gameState);
        
        // Save state
        GameState::saveState($room->id, $gameState->toArray());
        
        // Broadcast community card
        broadcast(new CommunityCardsDealt(
            [$communityCard->toArray()],
            $room->id
        ));
        
        // Broadcast updated state
        $publicState = $gameState->toArray();
        foreach ($publicState['players_state'] as &$playerState) {
            unset($playerState['cards']);
        }
        broadcast(new GameStateUpdated($room->id, $publicState));
        
        // Notify first player of their turn
        $this->notifyFirstPlayerOfTurn($room, $gameState);
    }

    /**
     * Advance to river stage
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    private function advanceToRiver(GameRoom $room, GameStateData $gameState): void
    {
        // Burn one card
        $deck = new DeckService();
        $deck->shuffle(); // We'll need to implement deck persistence in a real implementation
        
        // Draw one community card
        $communityCard = $deck->draw();
        
        // Update game state
        $gameState->phase = 'river';
        $gameState->communityCards[] = $communityCard->toArray();
        
        // Reset betting round
        $this->resetBettingRound($gameState);
        
        // Save state
        GameState::saveState($room->id, $gameState->toArray());
        
        // Broadcast community card
        broadcast(new CommunityCardsDealt(
            [$communityCard->toArray()],
            $room->id
        ));
        
        // Broadcast updated state
        $publicState = $gameState->toArray();
        foreach ($publicState['players_state'] as &$playerState) {
            unset($playerState['cards']);
        }
        broadcast(new GameStateUpdated($room->id, $publicState));
        
        // Notify first player of their turn
        $this->notifyFirstPlayerOfTurn($room, $gameState);
    }

    /**
     * Reset betting round for new stage
     *
     * @param GameStateData $gameState
     * @return void
     */
    private function resetBettingRound(GameStateData $gameState): void
    {
        // Reset current bet
        $gameState->currentBet = 0;
        
        // Reset player actions
        foreach ($gameState->playersState as &$playerState) {
            // Skip folded players
            if ($playerState['has_folded']) {
                continue;
            }
            
            $playerState['current_bet'] = 0;
            $playerState['has_acted'] = false;
        }
    }

    /**
     * Notify first player of their turn
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     */
    private function notifyFirstPlayerOfTurn(GameRoom $room, GameStateData $gameState): void
    {
        // Find first active player after dealer
        $dealerPosition = $gameState->dealerPosition;
        $playerStates = $gameState->playersState;
        
        // Convert to ordered array by seat
        $orderedPlayers = [];
        foreach ($playerStates as $playerState) {
            if (!$playerState['has_folded']) {
                $orderedPlayers[] = $playerState;
            }
        }
        
        // Sort by seat
        usort($orderedPlayers, function ($a, $b) {
            return $a['seat'] <=> $b['seat'];
        });
        
        // Find dealer index
        $dealerIndex = -1;
        for ($i = 0; $i < count($orderedPlayers); $i++) {
            if ($orderedPlayers[$i]['seat'] == $dealerPosition) {
                $dealerIndex = $i;
                break;
            }
        }
        
        // First player to act is after dealer
        $firstPlayerIndex = ($dealerIndex + 1) % count($orderedPlayers);
        $firstPlayer = $orderedPlayers[$firstPlayerIndex];
        
        // Set current player
        $gameState->currentPlayerId = $firstPlayer['id'];
        
        // Save updated state
        GameState::saveState($room->id, $gameState->toArray());
        
        // Notify player
        $playerState = $gameState->playersState[$firstPlayer['id']];
        $currentBet = $gameState->currentBet;
        $playerCurrentBet = $playerState['current_bet'];
        
        $callAmount = $currentBet - $playerCurrentBet;
        $minRaise = $currentBet * 2;
        $maxRaise = $playerState['stack'];
        
        $availableActions = ['fold', 'check', 'raise']; // Simplified for new round
        
        broadcast(new PlayerTurn(
            $firstPlayer['id'],
            $availableActions,
            $callAmount,
            $minRaise,
            $maxRaise
        ));
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
        
        // Clear game state
        GameState::deleteState($room->id);
    }
}