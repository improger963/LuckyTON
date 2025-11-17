<?php

namespace App\Services;

use App\Models\User;
use App\Models\GameRoom;
use App\Events\PlayerTurn;
use App\DTOs\GameStateData;
use App\Events\GameStarted;
use App\Jobs\StartNewGameRound;
use App\Events\GameStateUpdated;
use App\Events\PlayerJoinedRoom;
use App\Events\ShowdownOccurred;
use App\Events\PrivateCardsDealt;
use App\Services\Game\DeckService;
use App\Events\CommunityCardsDealt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Cache\GameCacheService;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\GameRoomRepository;
use App\Services\Game\PokerHands\HandEvaluatorService;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use Illuminate\Support\Collection;

class GameService
{
    /**
     * @var GameRoomRepository
     */
    protected $gameRoomRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var GameRoomPlayerRepository
     */
    protected $gameRoomPlayerRepository;

    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * @var HandEvaluatorService
     */
    protected $handEvaluator;

    /**
     * @var GameCacheService
     */
    protected $gameCacheService;

    /**
     * GameService constructor.
     *
     * @param GameRoomRepository $gameRoomRepository
     * @param UserRepository $userRepository
     * @param GameRoomPlayerRepository $gameRoomPlayerRepository
     * @param WalletService $walletService
     * @param GameCacheService $gameCacheService
     */
    public function __construct(
        GameRoomRepository $gameRoomRepository,
        UserRepository $userRepository,
        GameRoomPlayerRepository $gameRoomPlayerRepository,
        WalletService $walletService,
        GameCacheService $gameCacheService
    ) {
        $this->gameRoomRepository = $gameRoomRepository;
        $this->userRepository = $userRepository;
        $this->gameRoomPlayerRepository = $gameRoomPlayerRepository;
        $this->walletService = $walletService;
        $this->gameCacheService = $gameCacheService;
        $this->handEvaluator = new HandEvaluatorService();
    }

    /**
     * Get available game rooms grouped by type
     *
     * @return array
     */
    public function getAvailableRooms(): array
    {
        return $this->gameCacheService->getLobbyData();
    }

    /**
     * Join a user to a game room
     *
     * @param User $user
     * @param GameRoom $room
     * @return array
     * @throws \Exception
     */
    public function joinRoom(User $user, GameRoom $room): array
    {
        // Check if room is active (either waiting or in progress)
        if (!in_array($room->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS])) {
            throw new \Exception('This room is not available for joining.');
        }

        // Check if user is already in the room - if so, just return success (idempotent behavior)
        if ($room->players()->where('user_id', $user->id)->exists()) {
            // Reload room with players
            $roomWithPlayers = $this->loadRoomDetails($room);

            // Transform room data
            $currentPlayers = $roomWithPlayers->players->count();
            $transformedRoom = [
                'id' => $roomWithPlayers->id,
                'name' => $roomWithPlayers->name,
                'description' => $roomWithPlayers->description,
                'game_type' => $roomWithPlayers->game_type,
                'stake' => number_format($roomWithPlayers->stake, 0),
                'currency' => 'USD',
                'max_players' => $roomWithPlayers->max_players,
                'current_players' => $currentPlayers,
                'players_string' => $currentPlayers . '/' . $roomWithPlayers->max_players,
                'status' => $roomWithPlayers->status,
                'is_joinable' => in_array($roomWithPlayers->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS]),
                'players' => $roomWithPlayers->players->map(function ($player) {
                    return [
                        'id' => $player->id,
                        'username' => $player->username,
                        'avatar' => $player->avatar,
                        'role' => $player->pivot->role ?? 'spectator',
                        'seat_position' => $player->pivot->seat ?? null,
                        'stack' => $player->pivot->stack ?? 0,
                        'status' => 'active', // Default status
                    ];
                }),
                'created_at' => $roomWithPlayers->created_at?->toISOString(),
                'updated_at' => $roomWithPlayers->updated_at?->toISOString(),
            ];

            return [
                'message' => 'You have successfully joined the room.',
                'room' => $transformedRoom
            ];
        }

        // For in-progress rooms, users join as spectators only
        if ($room->status === GameRoom::STATUS_IN_PROGRESS) {
            // Add user to room as spectator
            $room->players()->attach($user->id, ['role' => 'spectator']);

            // Broadcast player joined event
            broadcast(new PlayerJoinedRoom($user, $room))->toOthers();

            // Reload room with players
            $roomWithPlayers = $this->loadRoomDetails($room);

            // Transform room data
            $currentPlayers = $roomWithPlayers->players->count();
            $transformedRoom = [
                'id' => $roomWithPlayers->id,
                'name' => $roomWithPlayers->name,
                'description' => $roomWithPlayers->description,
                'game_type' => $roomWithPlayers->game_type,
                'stake' => number_format($roomWithPlayers->stake, 0),
                'currency' => 'USD',
                'max_players' => $roomWithPlayers->max_players,
                'current_players' => $currentPlayers,
                'players_string' => $currentPlayers . '/' . $roomWithPlayers->max_players,
                'status' => $roomWithPlayers->status,
                'is_joinable' => in_array($roomWithPlayers->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS]),
                'players' => $roomWithPlayers->players->map(function ($player) {
                    return [
                        'id' => $player->id,
                        'username' => $player->username,
                        'avatar' => $player->avatar,
                        'role' => $player->pivot->role ?? 'spectator',
                        'seat_position' => $player->pivot->seat ?? null,
                        'stack' => $player->pivot->stack ?? 0,
                        'status' => 'active', // Default status
                    ];
                }),
                'created_at' => $roomWithPlayers->created_at?->toISOString(),
                'updated_at' => $roomWithPlayers->updated_at?->toISOString(),
            ];

            return [
                'message' => 'You have successfully joined the room as a spectator.',
                'room' => $transformedRoom
            ];
        }

        // For waiting rooms, check user balance before joining
        if ($user->wallet->balance < $room->stake) {
            throw new \Exception('Insufficient balance to join this room.');
        }

        // Deduct stake from user's balance
        $user->wallet->decrement('balance', $room->stake);

        // Create transaction record
        $user->wallet->transactions()->create([
            'type' => 'game_stake',
            'amount' => $room->stake,
            'description' => "Stake payment for room {$room->name}",
        ]);

        // Add user to room as player
        $room->players()->attach($user->id, ['role' => 'player']);

        // Broadcast player joined event
        broadcast(new PlayerJoinedRoom($user, $room))->toOthers();

        // Check if room has at least 2 players and start the game automatically
        // Load count of only players with 'player' role
        $room->loadCount(['players as players_count' => function ($query) {
            $query->where('role', 'player');
        }]);
        
        if ($room->players_count >= 2 && $room->status === GameRoom::STATUS_WAITING) {
            $room->status = GameRoom::STATUS_IN_PROGRESS;
            $room->save();

            broadcast(new GameStarted($room));

            // Start the poker game and deal cards
            $this->startPokerGame($room);
        }

        // Reload room with players
        $roomWithPlayers = $this->loadRoomDetails($room);

        // Transform room data
        $currentPlayers = $roomWithPlayers->players->count();
        $transformedRoom = [
            'id' => $roomWithPlayers->id,
            'name' => $roomWithPlayers->name,
            'description' => $roomWithPlayers->description,
            'game_type' => $roomWithPlayers->game_type,
            'stake' => number_format($roomWithPlayers->stake, 0),
            'currency' => 'USD',
            'max_players' => $roomWithPlayers->max_players,
            'current_players' => $currentPlayers,
            'players_string' => $currentPlayers . '/' . $roomWithPlayers->max_players,
            'status' => $roomWithPlayers->status,
            'is_joinable' => in_array($roomWithPlayers->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS]),
            'players' => $roomWithPlayers->players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'username' => $player->username,
                    'avatar' => $player->avatar,
                    'role' => $player->pivot->role ?? 'spectator',
                    'seat_position' => $player->pivot->seat ?? null,
                    'stack' => $player->pivot->stack ?? 0,
                    'status' => 'active', // Default status
                ];
            }),
            'created_at' => $roomWithPlayers->created_at?->toISOString(),
            'updated_at' => $roomWithPlayers->updated_at?->toISOString(),
        ];

        return [
            'message' => 'You have successfully joined the room.',
            'room' => $transformedRoom
        ];
    }

    /**
     * Start a poker game and deal cards
     *
     * @param GameRoom $room
     * @return void
     */
    public function startPokerGame(GameRoom $room): void
    {
        // Create a new deck and shuffle it
        $deck = new DeckService();
        $deck->shuffle();

        // Get all players in the room using repository
        /** @var \Illuminate\Support\Collection $players */
        $players = $this->gameRoomPlayerRepository->getPlayersWithUsers($room);

        // Deal two cards to each player
        foreach ($players as $player) {
            // Draw two cards from the deck
            $card1 = $deck->draw();
            $card2 = $deck->draw();

            // Create hand array
            $hand = [$card1, $card2];

            // Store private cards in cache for later use during showdown
            $cardsArray = [];
            foreach ($hand as $card) {
                if ($card) {
                    $cardsArray[] = $card->toArray();
                }
            }
            
            // Extract user ID safely
            $userId = is_object($player) ? $player->user_id : (is_array($player) ? $player['user_id'] : null);
            if ($userId) {
                Cache::put("private_cards_{$room->id}_{$userId}", $cardsArray, now()->addHours(1));

                // Broadcast the private cards to the specific player
                broadcast(new PrivateCardsDealt($hand, $userId));
            }
        }

        // Store the remaining deck in cache for future use (flop, turn, river)
        // We'll associate it with the room ID for retrieval later
        Cache::put("game_deck_{$room->id}", $deck, now()->addHours(1));

        // Initialize game state
        $this->initializeGameState($room, $players);
    }

    /**
     * Initialize the game state with positions, blinds, and first turn
     *
     * @param GameRoom $room
     * @param Collection $players
     * @return void
     */
    private function initializeGameState(GameRoom $room, Collection $players): void
    {
        // Convert players collection to array for count operation
        $playersArray = $players->toArray();
        
        // Get dealer position from meta state or default to 0
        $metaKey = "game_meta_{$room->id}";
        $metaState = Cache::get($metaKey, ['dealer_position' => 0]);
        $dealerPosition = $metaState['dealer_position'];
        
        // Rotate dealer position for next game
        $nextDealerPosition = ($dealerPosition + 1) % count($playersArray);
        
        // Save updated dealer position
        $metaState['dealer_position'] = $nextDealerPosition;
        Cache::put($metaKey, $metaState, now()->addHours(1));
        
        // Calculate blind positions based on new dealer position
        $sbPosition = ($nextDealerPosition + 1) % count($playersArray);
        $bbPosition = ($nextDealerPosition + 2) % count($playersArray);
        
        // Get small blind and big blind amounts
        $smallBlind = $room->stake / 2;
        $bigBlind = $room->stake;
        
        // Initialize players state
        $playersState = [];
        $currentPlayerId = 0;
        
        foreach ($playersArray as $index => $player) {
            // Determine if this player is SB or BB
            $isSB = ($index === $sbPosition);
            $isBB = ($index === $bbPosition);
            
            // Calculate initial bet
            $initialBet = 0;
            if ($isSB) {
                $initialBet = $smallBlind;
                // Deduct small blind from player's balance
                $this->deductBlind($player, $smallBlind);
            } elseif ($isBB) {
                $initialBet = $bigBlind;
                // Deduct big blind from player's balance
                $this->deductBlind($player, $bigBlind);
            }
            
            $playersState[] = [
                'id' => $player->user_id,
                'username' => $player->username,
                'stack' => $player->wallet_balance,
                'current_bet' => $initialBet,
                'has_folded' => false,
                'is_all_in' => false,
            ];
            
            // Determine who acts first (player after BB)
            if ($index === (($bbPosition + 1) % count($playersArray))) {
                $currentPlayerId = $player->user_id;
            }
        }
        
        // Calculate initial pot
        $pot = $smallBlind + $bigBlind;
        
        // Create game state data
        $gameState = new GameStateData(
            dealerPosition: $nextDealerPosition,
            currentPlayerId: $currentPlayerId,
            pot: $pot,
            playersState: $playersState,
            phase: 'preflop',
            currentBet: $bigBlind,
        );
        
        // Save game state to cache
        Cache::put("game_state_{$room->id}", $gameState, now()->addHours(1));
        
        // Broadcast game state to all players
        broadcast(new GameStateUpdated($room->id, $gameState->toArray()));
        
        // Broadcast turn to the current player
        $this->broadcastPlayerTurn($room, $currentPlayerId, $bigBlind);
    }

    /**
     * Deduct blind amount from player's balance
     *
     * @param $player
     * @param float $amount
     * @return void
     */
    private function deductBlind($player, float $amount): void
    {
        // Use the WalletService to properly deduct the blind amount
        try {
            $this->walletService->deductBlind($player->user_id, $amount, "Blind deduction for poker game");
            \Illuminate\Support\Facades\Log::info("Blind deduction successful", [
                'player_id' => $player->user_id,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Blind deduction failed", [
                'player_id' => $player->user_id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            // In a production environment, you might want to handle this more gracefully
            // For now, we'll just log the error and continue
        }
    }

    /**
     * Broadcast player turn with available actions
     *
     * @param GameRoom $room
     * @param int $currentPlayerId
     * @param float $currentBet
     * @return void
     */
    private function broadcastPlayerTurn(GameRoom $room, int $currentPlayerId, float $currentBet): void
    {
        // For now, we'll send basic actions
        // In a real implementation, this would be more sophisticated based on player's stack, etc.
        $availableActions = ['fold', 'call', 'raise'];
        $callAmount = $currentBet;
        $minRaise = $currentBet * 2;
        $maxRaise = 1000; // This should be based on player's stack in a real implementation
        
        broadcast(new PlayerTurn(
            userId: $currentPlayerId,
            availableActions: $availableActions,
            callAmount: $callAmount,
            minRaise: $minRaise,
            maxRaise: $maxRaise
        ));
    }

    /**
     * Process showdown to determine winners
     *
     * @param GameRoom $room
     * @return void
     */
    public function processShowdown(GameRoom $room): void
    {
        // Load game state from Redis
        $gameState = Cache::get("game_state_{$room->id}");
        
        if (!$gameState) {
            throw new \Exception("Game state not found for room {$room->id}");
        }
        
        // Convert to GameStateData object if it's an array
        if (is_array($gameState)) {
            $gameState = GameStateData::fromArray($gameState);
        }
        
        // Get community cards
        $communityCards = $gameState->communityCards;
        
        // Find all active players (not folded)
        $activePlayers = array_filter($gameState->playersState, function ($player) {
            return !$player['has_folded'];
        });
        
        // If there's only one active player, they win by default
        if (count($activePlayers) <= 1) {
            // Distribute the pot to the winner
            $winners = [];
            $potAmount = $gameState->pot;
            
            if (count($activePlayers) == 1) {
                $winner = reset($activePlayers);
                $winners[] = [
                    'id' => $winner['id'],
                    'amount_won' => $potAmount
                ];
            }
            
            // Broadcast showdown event
            broadcast(new ShowdownOccurred($room->id, [
                'players' => [],
                'community_cards' => $communityCards,
                'winner_by_default' => true
            ]));
            
            // Distribute pot
            $this->walletService->distributePot($room->id, $winners);
            
            // Start new game round after 5 seconds
            StartNewGameRound::dispatch($room)->delay(now()->addSeconds(5));
            
            return;
        }
        
        // For each active player, evaluate their best hand
        $playerResults = [];
        
        foreach ($activePlayers as $player) {
            // Get player's private cards from cache
            $privateCards = Cache::get("private_cards_{$room->id}_{$player['id']}");
            
            if (!$privateCards) {
                // Skip player if we can't find their cards
                continue;
            }
            
            // Combine private cards with community cards
            $sevenCards = array_merge($privateCards, array_map(function ($cardData) {
                // Convert array back to CardData object
                return new \App\DTOs\CardData(
                    \App\Enums\CardSuit::from($cardData['suit']),
                    \App\Enums\CardRank::from($cardData['rank'])
                );
            }, $communityCards));
            
            // Evaluate the best hand
            $handResult = $this->handEvaluator->evaluate($sevenCards);
            
            // Store result
            $playerResults[] = [
                'id' => $player['id'],
                'hand' => $privateCards,
                'combination' => $handResult->name,
                'rank' => $handResult->rank,
                'is_winner' => false // Will be set later
            ];
        }
        
        // Find the best hand(s)
        $maxRank = 0;
        foreach ($playerResults as $result) {
            if ($result['rank'] > $maxRank) {
                $maxRank = $result['rank'];
            }
        }
        
        // Mark winners and calculate pot distribution
        $winners = [];
        $winnerCount = 0;
        
        foreach ($playerResults as &$result) {
            if ($result['rank'] === $maxRank) {
                $result['is_winner'] = true;
                $winnerCount++;
            }
        }
        
        // Distribute pot equally among winners
        $potAmount = $gameState->pot;
        $amountPerWinner = $potAmount / $winnerCount;
        
        foreach ($playerResults as $result) {
            if ($result['is_winner']) {
                $winners[] = [
                    'id' => $result['id'],
                    'amount_won' => $amountPerWinner
                ];
            }
        }
        
        // Update game state
        $gameState->phase = 'showdown';
        Cache::put("game_state_{$room->id}", $gameState, now()->addHours(1));
        
        // Broadcast showdown event
        broadcast(new ShowdownOccurred($room->id, [
            'players' => $playerResults,
            'community_cards' => $communityCards
        ]));
        
        // Distribute pot
        $this->walletService->distributePot($room->id, $winners);
        
        // Start new game round after 5 seconds
        StartNewGameRound::dispatch($room)->delay(now()->addSeconds(5));
    }

    /**
     * Advance to next stage of the game
     *
     * @param GameRoom $room
     * @return void
     * @throws \Exception
     */
    public function advanceToNextStage(GameRoom $room): void
    {
        // Load game state from Redis
        $gameState = Cache::get("game_state_{$room->id}");
        
        if (!$gameState) {
            throw new \Exception("Game state not found for room {$room->id}");
        }
        
        // Convert to GameStateData object if it's an array
        if (is_array($gameState)) {
            $gameState = GameStateData::fromArray($gameState);
        }
        
        // Determine current stage and what to do next
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
                $this->processShowdown($room);
                break;
        }
    }
    
    /**
     * Advance from preflop to flop
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     * @throws \Exception
     */
    private function advanceToFlop(GameRoom $room, GameStateData $gameState): void
    {
        // Load deck from cache
        $deck = Cache::get("game_deck_{$room->id}");
        
        if (!$deck) {
            throw new \Exception("Deck not found for room {$room->id}");
        }
        
        // Burn one card
        $deck->draw();
        
        // Draw three cards for the flop
        $flopCards = $deck->drawMultiple(3);
        
        // Add these cards to the community cards
        $flopCardsArray = [];
        foreach ($flopCards as $card) {
            $flopCardsArray[] = $card->toArray();
        }
        $gameState->communityCards = array_merge($gameState->communityCards, $flopCardsArray);
        
        // Reset current bets for all players (but keep the pot)
        foreach ($gameState->playersState as &$playerState) {
            $playerState['current_bet'] = 0;
        }
        
        // Update game phase
        $gameState->phase = 'flop';
        $gameState->currentBet = 0;
        
        // Determine who acts first on the flop (first active player after dealer)
        $firstPlayerId = $this->getFirstActivePlayerAfterDealer($gameState);
        $gameState->currentPlayerId = $firstPlayerId;
        
        // Save updated game state to cache
        Cache::put("game_state_{$room->id}", $gameState, now()->addHours(1));
        
        // Save remaining deck back to cache
        Cache::put("game_deck_{$room->id}", $deck, now()->addHours(1));
        
        // Broadcast events
        $flopCardsToArray = [];
        foreach ($flopCards as $card) {
            $flopCardsToArray[] = $card->toArray();
        }
        broadcast(new CommunityCardsDealt($flopCardsToArray, $room->id));
        broadcast(new GameStateUpdated($room->id, $gameState->toArray()));
        $this->broadcastPlayerTurn($room, $firstPlayerId, $gameState->currentBet);
    }
    
    /**
     * Advance from flop to turn
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     * @throws \Exception
     */
    private function advanceToTurn(GameRoom $room, GameStateData $gameState): void
    {
        // Load deck from cache
        $deck = Cache::get("game_deck_{$room->id}");
        
        if (!$deck) {
            throw new \Exception("Deck not found for room {$room->id}");
        }
        
        // Burn one card
        $deck->draw();
        
        // Draw one card for the turn
        $turnCard = $deck->draw();
        
        // Add this card to the community cards
        $gameState->communityCards[] = $turnCard->toArray();
        
        // Reset current bets for all players (but keep the pot)
        foreach ($gameState->playersState as &$playerState) {
            $playerState['current_bet'] = 0;
        }
        
        // Update game phase
        $gameState->phase = 'turn';
        $gameState->currentBet = 0;
        
        // Determine who acts first on the turn (first active player after dealer)
        $firstPlayerId = $this->getFirstActivePlayerAfterDealer($gameState);
        $gameState->currentPlayerId = $firstPlayerId;
        
        // Save updated game state to cache
        Cache::put("game_state_{$room->id}", $gameState, now()->addHours(1));
        
        // Save remaining deck back to cache
        Cache::put("game_deck_{$room->id}", $deck, now()->addHours(1));
        
        // Broadcast events
        broadcast(new CommunityCardsDealt([$turnCard->toArray()], $room->id));
        broadcast(new GameStateUpdated($room->id, $gameState->toArray()));
        $this->broadcastPlayerTurn($room, $firstPlayerId, $gameState->currentBet);
    }
    
    /**
     * Advance from turn to river
     *
     * @param GameRoom $room
     * @param GameStateData $gameState
     * @return void
     * @throws \Exception
     */
    private function advanceToRiver(GameRoom $room, GameStateData $gameState): void
    {
        // Load deck from cache
        $deck = Cache::get("game_deck_{$room->id}");
        
        if (!$deck) {
            throw new \Exception("Deck not found for room {$room->id}");
        }
        
        // Burn one card
        $deck->draw();
        
        // Draw one card for the river
        $riverCard = $deck->draw();
        
        // Add this card to the community cards
        $gameState->communityCards[] = $riverCard->toArray();
        
        // Reset current bets for all players (but keep the pot)
        foreach ($gameState->playersState as &$playerState) {
            $playerState['current_bet'] = 0;
        }
        
        // Update game phase
        $gameState->phase = 'river';
        $gameState->currentBet = 0;
        
        // Determine who acts first on the river (first active player after dealer)
        $firstPlayerId = $this->getFirstActivePlayerAfterDealer($gameState);
        $gameState->currentPlayerId = $firstPlayerId;
        
        // Save updated game state to cache
        Cache::put("game_state_{$room->id}", $gameState, now()->addHours(1));
        
        // Save remaining deck back to cache
        Cache::put("game_deck_{$room->id}", $deck, now()->addHours(1));
        
        // Broadcast events
        broadcast(new CommunityCardsDealt([$riverCard->toArray()], $room->id));
        broadcast(new GameStateUpdated($room->id, $gameState->toArray()));
        $this->broadcastPlayerTurn($room, $firstPlayerId, $gameState->currentBet);
    }
    
    /**
     * Get the first active player after the dealer
     *
     * @param GameStateData $gameState
     * @return int
     */
    private function getFirstActivePlayerAfterDealer(GameStateData $gameState): int
    {
        $dealerPosition = $gameState->dealerPosition;
        $playerCount = count($gameState->playersState);
        
        // Handle edge case where there are no players
        if ($playerCount === 0) {
            throw new \Exception("No players in the game state");
        }
        
        // Start from the player after the dealer
        for ($i = 1; $i <= $playerCount; $i++) {
            $position = ($dealerPosition + $i) % $playerCount;
            
            // Check if position exists in playersState
            if (!isset($gameState->playersState[$position])) {
                continue;
            }
            
            $player = $gameState->playersState[$position];
            
            // Return the first active player (not folded)
            if (isset($player['has_folded']) && !$player['has_folded']) {
                return $player['id'];
            }
        }
        
        // If no active player found, return the dealer (if exists)
        if (isset($gameState->playersState[$dealerPosition])) {
            return $gameState->playersState[$dealerPosition]['id'];
        }
        
        // Fallback - return first player's ID if available
        foreach ($gameState->playersState as $player) {
            if (isset($player['id'])) {
                return $player['id'];
            }
        }
        
        // If we reach here, something is seriously wrong
        throw new \Exception("Unable to determine active player");
    }

    /**
     * Get formatted game room details
     *
     * @param GameRoom $room
     * @return array
     */
    public function getFormattedRoomDetails(GameRoom $room): array
    {
        $roomWithPlayers = $this->loadRoomDetails($room);

        // Transform room data
        $currentPlayers = $roomWithPlayers->players->count();
        $transformedRoom = [
            'id' => $roomWithPlayers->id,
            'name' => $roomWithPlayers->name,
            'description' => $roomWithPlayers->description,
            'game_type' => $roomWithPlayers->game_type,
            'stake' => number_format($roomWithPlayers->stake, 0),
            'currency' => 'USD',
            'max_players' => $roomWithPlayers->max_players,
            'current_players' => $currentPlayers,
            'players_string' => $currentPlayers . '/' . $roomWithPlayers->max_players,
            'status' => $roomWithPlayers->status,
            'is_joinable' => in_array($roomWithPlayers->status, [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS]),
            'players' => $roomWithPlayers->players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'username' => $player->username,
                    'avatar' => $player->avatar,
                    'role' => $player->pivot->role ?? 'spectator',
                    'seat_position' => $player->pivot->seat ?? null,
                    'stack' => $player->pivot->stack ?? 0,
                    'status' => 'active', // Default status
                ];
            }),
            'created_at' => $roomWithPlayers->created_at?->toISOString(),
            'updated_at' => $roomWithPlayers->updated_at?->toISOString(),
        ];

        return $transformedRoom;
    }

    /**
     * Format room data for response
     *
     * @param GameRoom $room
     * @return array
     */
    private function formatRoomData(GameRoom $room): array
    {
        return [
            'id' => $room->id,
            'name' => $room->name,
            'game_type' => $room->game_type,
            'stake' => $room->stake,
            'max_players' => $room->max_players,
            'current_players' => $room->players_count,
            'status' => $room->status,
        ];
    }

    /**
     * Get room details with players
     *
     * @param GameRoom $room
     * @return GameRoom
     */
    private function loadRoomDetails(GameRoom $room): GameRoom
    {
        return $room->load(['players' => function ($query) {
            $query->select('users.id', 'users.username', 'users.avatar')
                  ->withPivot(['role', 'seat', 'stack']);
        }]);
    }

    /**
     * Get the game room player repository
     *
     * @return GameRoomPlayerRepository
     */
    public function getGameRoomPlayerRepository(): GameRoomPlayerRepository
    {
        return $this->gameRoomPlayerRepository;
    }

    /**
     * Get the wallet service
     *
     * @return WalletService
     */
    public function getWalletService(): WalletService
    {
        return $this->walletService;
    }

}