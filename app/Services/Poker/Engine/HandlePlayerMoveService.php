<?php

namespace App\Services\Poker\Engine;

use App\Models\GameRoom;
use App\Models\GameState;
use App\DTOs\GameStateData;
use App\Services\WalletService;
use App\Services\TransactionService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Events\GameStateUpdated;
use App\Events\PlayerTurn;
use App\Events\PotDistributed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HandlePlayerMoveService
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
     * Handle a player's move
     *
     * @param GameRoom $room
     * @param int $userId
     * @param string $action
     * @param float $amount
     * @return void
     */
    public function execute(GameRoom $room, int $userId, string $action, float $amount = 0): void
    {
        Log::info('[POKER ENGINE] Handling player move for room: ' . $room->id, [
            'user_id' => $userId,
            'action' => $action,
            'amount' => $amount
        ]);

        try {
            DB::transaction(function () use ($room, $userId, $action, $amount) {
                // Load current game state
                $stateData = GameState::loadState($room->id);
                
                if (!$stateData) {
                    throw new \Exception('No active game state found');
                }
                
                $gameState = GameStateData::fromArray($stateData);
                
                // Validate it's the correct player's turn
                if ($gameState->currentPlayerId !== $userId) {
                    throw new \Exception('It is not your turn');
                }
                
                // Get player state
                $playerState = $gameState->playersState[$userId] ?? null;
                
                if (!$playerState) {
                    throw new \Exception('Player not found in game');
                }
                
                // Validate action
                $this->validateAction($gameState, $playerState, $action, $amount);
                
                // Process the action
                $this->processAction($gameState, $userId, $action, $amount);
                
                // Check if round is complete
                if ($this->isBettingRoundComplete($gameState)) {
                    // Move to next stage
                    $advanceService = new AdvanceToNextStageService(
                        $this->gameRoomPlayerRepository,
                        $this->walletService,
                        $this->transactionService
                    );
                    $advanceService->execute($room, $gameState);
                } else {
                    // Determine next player
                    $nextPlayerId = $this->determineNextPlayer($gameState);
                    
                    if ($nextPlayerId) {
                        $gameState->currentPlayerId = $nextPlayerId;
                        
                        // Save updated state
                        GameState::saveState($room->id, $gameState->toArray());
                        
                        // Broadcast updated state
                        $publicState = $gameState->toArray();
                        foreach ($publicState['players_state'] as &$playerState) {
                            unset($playerState['cards']);
                        }
                        broadcast(new GameStateUpdated($room->id, $publicState));
                        
                        // Notify next player
                        $this->notifyPlayerOfTurn($room, $gameState, $nextPlayerId);
                    } else {
                        // No more players, should move to next stage
                        $advanceService = new AdvanceToNextStageService(
                            $this->gameRoomPlayerRepository,
                            $this->walletService,
                            $this->transactionService
                        );
                        $advanceService->execute($room, $gameState);
                    }
                }
            });
            
            Log::info('[POKER ENGINE] Player move handled successfully for room: ' . $room->id);
        } catch (\Exception $e) {
            Log::error('[POKER ENGINE] Failed to handle player move for room: ' . $room->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Validate player action
     *
     * @param GameStateData $gameState
     * @param array $playerState
     * @param string $action
     * @param float $amount
     * @return void
     */
    private function validateAction(GameStateData $gameState, array $playerState, string $action, float $amount): void
    {
        $availableActions = $this->getAvailableActions($gameState, $playerState);
        
        if (!in_array($action, $availableActions)) {
            throw new \Exception("Action {$action} is not available");
        }
        
        // Additional validation for raise
        if ($action === 'raise') {
            $minRaise = $gameState->currentBet * 2;
            $maxRaise = $playerState['stack'];
            
            if ($amount < $minRaise) {
                throw new \Exception("Raise amount must be at least {$minRaise}");
            }
            
            if ($amount > $maxRaise) {
                throw new \Exception("Raise amount cannot exceed your stack of {$maxRaise}");
            }
        }
    }

    /**
     * Get available actions for player
     *
     * @param GameStateData $gameState
     * @param array $playerState
     * @return array
     */
    private function getAvailableActions(GameStateData $gameState, array $playerState): array
    {
        $actions = ['fold'];
        $playerStack = $playerState['stack'];
        $playerCurrentBet = $playerState['current_bet'];
        $currentBet = $gameState->currentBet;
        
        $callAmount = $currentBet - $playerCurrentBet;
        
        if ($callAmount <= $playerStack) {
            $actions[] = 'call';
        }
        
        $minRaise = $currentBet * 2;
        if ($minRaise <= $playerStack) {
            $actions[] = 'raise';
        }
        
        if ($callAmount == 0) {
            $actions[] = 'check';
        }
        
        return $actions;
    }

    /**
     * Process player action
     *
     * @param GameStateData $gameState
     * @param int $userId
     * @param string $action
     * @param float $amount
     * @return void
     */
    private function processAction(GameStateData $gameState, int $userId, string $action, float $amount): void
    {
        $playerState = &$gameState->playersState[$userId];
        $playerStack = $playerState['stack'];
        $playerCurrentBet = $playerState['current_bet'];
        $currentBet = $gameState->currentBet;
        
        switch ($action) {
            case 'fold':
                $playerState['has_folded'] = true;
                $playerState['has_acted'] = true;
                break;
                
            case 'check':
                if ($playerCurrentBet < $currentBet) {
                    throw new \Exception('Cannot check when there is a bet to call');
                }
                $playerState['has_acted'] = true;
                break;
                
            case 'call':
                $callAmount = $currentBet - $playerCurrentBet;
                if ($callAmount > $playerStack) {
                    // Player goes all-in
                    $callAmount = $playerStack;
                    $playerState['is_all_in'] = true;
                }
                
                $playerState['stack'] -= $callAmount;
                $playerState['current_bet'] += $callAmount;
                $playerState['has_acted'] = true;
                $gameState->pot += $callAmount;
                break;
                
            case 'raise':
                $raiseAmount = $amount - $playerCurrentBet;
                if ($raiseAmount > $playerStack) {
                    // Player goes all-in
                    $raiseAmount = $playerStack;
                    $playerState['is_all_in'] = true;
                }
                
                $playerState['stack'] -= $raiseAmount;
                $playerState['current_bet'] += $raiseAmount;
                $playerState['has_acted'] = true;
                $gameState->pot += $raiseAmount;
                $gameState->currentBet = $amount;
                
                // Reset has_acted for all other players
                foreach ($gameState->playersState as &$otherPlayerState) {
                    if ($otherPlayerState['id'] !== $userId && !$otherPlayerState['has_folded']) {
                        $otherPlayerState['has_acted'] = false;
                    }
                }
                break;
        }
    }

    /**
     * Check if betting round is complete
     *
     * @param GameStateData $gameState
     * @return bool
     */
    private function isBettingRoundComplete(GameStateData $gameState): bool
    {
        $activePlayers = array_filter($gameState->playersState, function ($player) {
            return !$player['has_folded'] && !$player['is_all_in'];
        });
        
        // If only one active player, round is complete
        if (count($activePlayers) <= 1) {
            return true;
        }
        
        // Check if all active players have acted and have the same current bet
        $currentBet = $gameState->currentBet;
        $allActed = true;
        $allMatchedBet = true;
        
        foreach ($gameState->playersState as $player) {
            // Skip folded players
            if ($player['has_folded']) {
                continue;
            }
            
            // Check if player has acted
            if (!$player['has_acted']) {
                $allActed = false;
            }
            
            // Check if player has matched the current bet (unless all-in)
            if (!$player['is_all_in'] && $player['current_bet'] < $currentBet) {
                $allMatchedBet = false;
            }
        }
        
        return $allActed && $allMatchedBet;
    }

    /**
     * Determine next player to act
     *
     * @param GameStateData $gameState
     * @return int|null
     */
    private function determineNextPlayer(GameStateData $gameState): ?int
    {
        $playerStates = $gameState->playersState;
        $currentPlayerId = $gameState->currentPlayerId;
        
        // Convert to ordered array by seat
        $orderedPlayers = [];
        foreach ($playerStates as $playerState) {
            if (!$playerState['has_folded'] && !$playerState['is_all_in']) {
                $orderedPlayers[] = $playerState;
            }
        }
        
        // Sort by seat
        usort($orderedPlayers, function ($a, $b) {
            return $a['seat'] <=> $b['seat'];
        });
        
        // Find current player index
        $currentIndex = -1;
        for ($i = 0; $i < count($orderedPlayers); $i++) {
            if ($orderedPlayers[$i]['id'] == $currentPlayerId) {
                $currentIndex = $i;
                break;
            }
        }
        
        // Find next player who hasn't acted
        for ($i = 1; $i <= count($orderedPlayers); $i++) {
            $nextIndex = ($currentIndex + $i) % count($orderedPlayers);
            $nextPlayer = $orderedPlayers[$nextIndex];
            
            if (!$nextPlayer['has_acted']) {
                return $nextPlayer['id'];
            }
        }
        
        // No player found who hasn't acted
        return null;
    }

    /**
     * Notify player of their turn
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param int $playerId
     * @return void
     */
    private function notifyPlayerOfTurn(GameRoom $room, GameStateData $gameState, int $playerId): void
    {
        $playerState = $gameState->playersState[$playerId];
        $currentBet = $gameState->currentBet;
        $playerCurrentBet = $playerState['current_bet'];
        
        $callAmount = $currentBet - $playerCurrentBet;
        $minRaise = $currentBet * 2;
        $maxRaise = $playerState['stack'];
        
        $availableActions = $this->getAvailableActions($gameState, $playerState);
        
        broadcast(new PlayerTurn(
            $playerId,
            $availableActions,
            $callAmount,
            $minRaise,
            $maxRaise
        ));
    }
}