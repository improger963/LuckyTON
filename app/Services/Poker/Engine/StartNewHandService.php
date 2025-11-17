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
use App\Events\PrivateCardsDealt;
use App\Events\CommunityCardsDealt;
use App\Events\PlayerTurn;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StartNewHandService
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
     * Start a new hand in a game room
     *
     * @param GameRoom $room
     * @return void
     */
    public function execute(GameRoom $room): void
    {
        Log::info('[POKER ENGINE] Starting new hand for room: ' . $room->id);

        try {
            DB::transaction(function () use ($room) {
                // Get active players
                $players = $this->gameRoomPlayerRepository->getActivePlayers($room);
                
                if ($players->count() < 2) {
                    throw new \Exception('Not enough players to start a hand');
                }
                
                // Step 1: Clean up previous hand state
                $this->cleanupPreviousHand($room);
                
                // Step 2: Rotate positions
                $dealerPosition = $this->rotateDealerPosition($room, $players);
                
                // Step 3: Collect blinds
                $blindsData = $this->collectBlinds($room, $players, $dealerPosition);
                
                // Step 4: Prepare deck
                $deck = new DeckService();
                $deck->shuffle();
                
                // Step 5: Deal pocket cards
                $playersWithCards = $this->dealPocketCards($deck, $players);
                
                // Step 6: Create initial game state
                $gameState = $this->createInitialGameState(
                    $room, 
                    $playersWithCards, 
                    $dealerPosition, 
                    $blindsData
                );
                
                // Step 7: Save game state
                GameState::saveState($room->id, $gameState->toArray());
                
                // Step 8: Broadcast initial state
                $this->broadcastInitialState($room, $gameState, $playersWithCards);
                
                // Step 9: Notify first player of their turn
                $this->notifyFirstPlayer($room, $gameState, $playersWithCards);
            });
            
            Log::info('[POKER ENGINE] New hand started successfully for room: ' . $room->id);
        } catch (\Exception $e) {
            Log::error('[POKER ENGINE] Failed to start new hand for room: ' . $room->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Clean up previous hand state
     *
     * @param GameRoom $room
     * @return void
     */
    private function cleanupPreviousHand(GameRoom $room): void
    {
        // Reset any previous game state
        GameState::deleteState($room->id);
    }

    /**
     * Rotate dealer position
     *
     * @param GameRoom $room
     * @param \Illuminate\Support\Collection $players
     * @return int
     */
    private function rotateDealerPosition(GameRoom $room, $players): int
    {
        // Get current dealer position from game state or default to first player
        $currentState = GameState::loadState($room->id);
        $currentDealerPosition = $currentState['dealer_position'] ?? 0;
        
        // Find next active player position
        $playerPositions = $players->pluck('pivot.seat')->sort()->values();
        $positions = $playerPositions->toArray();
        
        // If no current dealer or current dealer is last position, go to first
        if ($currentDealerPosition === 0 || $currentDealerPosition >= max($positions)) {
            return $positions[0];
        }
        
        // Find next position
        foreach ($positions as $position) {
            if ($position > $currentDealerPosition) {
                return $position;
            }
        }
        
        // Fallback to first position
        return $positions[0];
    }

    /**
     * Collect blinds from players
     *
     * @param GameRoom $room
     * @param \Illuminate\Support\Collection $players
     * @param int $dealerPosition
     * @return array
     */
    private function collectBlinds(GameRoom $room, $players, int $dealerPosition): array
    {
        $playerSeats = $players->keyBy('pivot.seat');
        $positions = $playerSeats->keys()->sort()->values()->toArray();
        
        // In heads-up, dealer is SB
        if (count($positions) === 2) {
            $sbPosition = $dealerPosition;
        } else {
            // SB is player after dealer
            $dealerIndex = array_search($dealerPosition, $positions);
            $sbPosition = $positions[($dealerIndex + 1) % count($positions)];
        }
        
        // BB is player after SB
        $sbIndex = array_search($sbPosition, $positions);
        $bbPosition = $positions[($sbIndex + 1) % count($positions)];
        
        $blinds = [
            'small_blind' => $room->stake / 2,
            'big_blind' => $room->stake,
            'sb_position' => $sbPosition,
            'bb_position' => $bbPosition
        ];
        
        // Collect blinds from players
        $sbPlayer = $playerSeats->get($sbPosition);
        $bbPlayer = $playerSeats->get($bbPosition);
        
        // Collect SB
        if ($sbPlayer) {
            $this->collectBlindFromPlayer($sbPlayer, $blinds['small_blind'], 'small_blind');
        }
        
        // Collect BB
        if ($bbPlayer) {
            $this->collectBlindFromPlayer($bbPlayer, $blinds['big_blind'], 'big_blind');
        }
        
        return $blinds;
    }

    /**
     * Collect blind from a specific player
     *
     * @param mixed $player
     * @param float $amount
     * @param string $type
     * @return void
     */
    private function collectBlindFromPlayer($player, float $amount, string $type): void
    {
        $userId = $player->id;
        
        // Check if player has enough chips
        $stack = $player->pivot->stack ?? 0;
        
        if ($stack <= $amount) {
            // Player goes all-in
            $actualAmount = $stack;
            $this->markPlayerAllIn($player);
        } else {
            $actualAmount = $amount;
        }
        
        // Deduct from player stack
        $this->gameRoomPlayerRepository->updatePlayerStack($player->pivot->game_room_id, $userId, -$actualAmount);
        
        // Create game stake transaction
        $this->transactionService->createTransaction(
            $player->wallet,
            'game_stake',
            -$actualAmount,
            "Poker {$type} payment"
        );
    }

    /**
     * Mark player as all-in
     *
     * @param mixed $player
     * @return void
     */
    private function markPlayerAllIn($player): void
    {
        // Implementation would update player status to all-in
        // This would be handled in the game state
    }

    /**
     * Deal pocket cards to players
     *
     * @param DeckService $deck
     * @param \Illuminate\Support\Collection $players
     * @return array
     */
    private function dealPocketCards(DeckService $deck, $players): array
    {
        $playersWithCards = [];
        
        foreach ($players as $player) {
            $cards = $deck->drawMultiple(2)->toArray();
            $playersWithCards[] = [
                'player' => $player,
                'cards' => $cards
            ];
        }
        
        return $playersWithCards;
    }

    /**
     * Create initial game state
     *
     * @param GameRoom $room
     * @param array $playersWithCards
     * @param int $dealerPosition
     * @param array $blindsData
     * @return GameStateData
     */
    private function createInitialGameState(
        GameRoom $room, 
        array $playersWithCards, 
        int $dealerPosition, 
        array $blindsData
    ): GameStateData {
        $playersState = [];
        
        foreach ($playersWithCards as $playerData) {
            $player = $playerData['player'];
            $seat = $player->pivot->seat;
            
            $playersState[$player->id] = [
                'id' => $player->id,
                'username' => $player->username,
                'seat' => $seat,
                'stack' => $player->pivot->stack - ($seat === $blindsData['sb_position'] ? $blindsData['small_blind'] : 0) - ($seat === $blindsData['bb_position'] ? $blindsData['big_blind'] : 0),
                'has_folded' => false,
                'has_called' => $seat === $blindsData['bb_position'],
                'has_acted' => $seat === $blindsData['sb_position'] || $seat === $blindsData['bb_position'],
                'current_bet' => $seat === $blindsData['sb_position'] ? $blindsData['small_blind'] : ($seat === $blindsData['bb_position'] ? $blindsData['big_blind'] : 0),
                'is_all_in' => false,
                'cards' => array_map(fn($card) => $card->toArray(), $playerData['cards'])
            ];
        }
        
        return new GameStateData(
            dealerPosition: $dealerPosition,
            currentPlayerId: $this->determineFirstPlayerToAct($playersWithCards, $dealerPosition, $blindsData),
            pot: $blindsData['small_blind'] + $blindsData['big_blind'],
            playersState: $playersState,
            phase: 'preflop',
            currentBet: $blindsData['big_blind'],
            communityCards: []
        );
    }

    /**
     * Determine first player to act (UTG)
     *
     * @param array $playersWithCards
     * @param int $dealerPosition
     * @param array $blindsData
     * @return int
     */
    private function determineFirstPlayerToAct(array $playersWithCards, int $dealerPosition, array $blindsData): int
    {
        $playerSeats = collect($playersWithCards)->mapWithKeys(function ($playerData) {
            return [$playerData['player']->pivot->seat => $playerData['player']->id];
        });
        
        $positions = $playerSeats->keys()->sort()->values()->toArray();
        
        // Find position after BB
        $bbIndex = array_search($blindsData['bb_position'], $positions);
        $utgIndex = ($bbIndex + 1) % count($positions);
        $utgPosition = $positions[$utgIndex];
        
        return $playerSeats->get($utgPosition);
    }

    /**
     * Broadcast initial game state
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param array $playersWithCards
     * @return void
     */
    private function broadcastInitialState(GameRoom $room, GameStateData $gameState, array $playersWithCards): void
    {
        // Broadcast private cards to each player
        foreach ($playersWithCards as $playerData) {
            $player = $playerData['player'];
            $cards = $playerData['cards'];
            
            broadcast(new PrivateCardsDealt($cards, $player->id));
        }
        
        // Broadcast public game state (without private cards)
        $publicState = $gameState->toArray();
        foreach ($publicState['players_state'] as &$playerState) {
            unset($playerState['cards']);
        }
        
        broadcast(new GameStateUpdated($room->id, $publicState));
    }

    /**
     * Notify first player of their turn
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @param array $playersWithCards
     * @return void
     */
    private function notifyFirstPlayer(GameRoom $room, GameStateData $gameState, array $playersWithCards): void
    {
        $currentPlayerId = $gameState->currentPlayerId;
        $currentBet = $gameState->currentBet;
        $pot = $gameState->pot;
        
        // Find current player
        $currentPlayer = null;
        foreach ($playersWithCards as $playerData) {
            if ($playerData['player']->id == $currentPlayerId) {
                $currentPlayer = $playerData['player'];
                break;
            }
        }
        
        if ($currentPlayer) {
            // Calculate available actions
            $playerState = $gameState->playersState[$currentPlayerId];
            $playerStack = $playerState['stack'];
            $playerCurrentBet = $playerState['current_bet'];
            
            $callAmount = $currentBet - $playerCurrentBet;
            $minRaise = $currentBet * 2;
            $maxRaise = $playerStack;
            
            $availableActions = ['fold'];
            
            if ($callAmount <= $playerStack) {
                $availableActions[] = 'call';
            }
            
            if ($minRaise <= $playerStack) {
                $availableActions[] = 'raise';
            }
            
            if ($callAmount == 0) {
                $availableActions[] = 'check';
            }
            
            broadcast(new PlayerTurn(
                $currentPlayerId,
                $availableActions,
                $callAmount,
                $minRaise,
                $maxRaise
            ));
        }
    }
}