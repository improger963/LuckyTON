<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\GameRoom;
use App\Models\GameState as GameStateModel;
use App\Repositories\Eloquent\GameRoomRepository;
use Illuminate\Support\Facades\Log;

class GameCacheService
{
    /**
     * Cache key prefixes
     */
    const LOBBY_DATA_KEY = 'lobby_data';
    const GAME_STATE_KEY = 'game_state';
    const GAME_DECK_KEY = 'game_deck';
    const PRIVATE_CARDS_KEY = 'private_cards';
    const GAME_META_KEY = 'game_meta';
    
    /**
     * Cache TTL in seconds
     */
    const LOBBY_DATA_TTL = 30;  // 30 seconds for lobby data
    const GAME_STATE_TTL = 3600; // 1 hour for game states
    const GAME_DECK_TTL = 3600;  // 1 hour for game decks
    const PRIVATE_CARDS_TTL = 3600; // 1 hour for private cards
    const GAME_META_TTL = 3600;  // 1 hour for game meta data

    /**
     * @var GameRoomRepository
     */
    protected $gameRoomRepository;

    /**
     * GameCacheService constructor.
     *
     * @param GameRoomRepository $gameRoomRepository
     */
    public function __construct(GameRoomRepository $gameRoomRepository)
    {
        $this->gameRoomRepository = $gameRoomRepository;
    }

    /**
     * Cache lobby data (available game rooms grouped by type)
     *
     * @return array
     */
    public function getLobbyData(): array
    {
        return Cache::remember(
            self::LOBBY_DATA_KEY,
            self::LOBBY_DATA_TTL,
            function () {
                $rooms = $this->gameRoomRepository->getAvailableRoomsWithPlayerCount();
                
                // Group rooms by game type
                $groupedRooms = $rooms->groupBy('game_type');
                
                // Transform data for response
                $response = [];
                foreach ($groupedRooms as $gameType => $roomsOfType) {
                    $response[$gameType] = [
                        'game_type' => $gameType,
                        'rooms' => $roomsOfType->map(function ($room) {
                            return [
                                'id' => $room->id,
                                'name' => $room->name,
                                'stake' => $room->stake,
                                'max_players' => $room->max_players,
                                'current_players' => $room->players_count,
                                'players_string' => $room->players_count . '/' . $room->max_players,
                                'status' => $room->status,
                                'is_joinable_as_player' => $room->players_count < $room->max_players && $room->status === 'waiting',
                            ];
                        }),
                    ];
                }
                
                return $response;
            }
        );
    }

    /**
     * Clear lobby data cache
     *
     * @return void
     */
    public function clearLobbyData(): void
    {
        Cache::forget(self::LOBBY_DATA_KEY);
    }

    /**
     * Cache game state
     *
     * @param int $roomId
     * @param mixed $gameState
     * @return void
     */
    public function cacheGameState(int $roomId, $gameState): void
    {
        Cache::put(
            $this->getGameStateKey($roomId),
            $gameState,
            self::GAME_STATE_TTL
        );
    }

    /**
     * Get cached game state
     *
     * @param int $roomId
     * @return mixed
     */
    public function getGameState(int $roomId)
    {
        return Cache::get($this->getGameStateKey($roomId));
    }

    /**
     * Clear game state cache
     *
     * @param int $roomId
     * @return void
     */
    public function clearGameState(int $roomId): void
    {
        Cache::forget($this->getGameStateKey($roomId));
    }

    /**
     * Cache game deck
     *
     * @param int $roomId
     * @param mixed $deck
     * @return void
     */
    public function cacheGameDeck(int $roomId, $deck): void
    {
        Cache::put(
            $this->getGameDeckKey($roomId),
            $deck,
            self::GAME_DECK_TTL
        );
    }

    /**
     * Get cached game deck
     *
     * @param int $roomId
     * @return mixed
     */
    public function getGameDeck(int $roomId)
    {
        return Cache::get($this->getGameDeckKey($roomId));
    }

    /**
     * Clear game deck cache
     *
     * @param int $roomId
     * @return void
     */
    public function clearGameDeck(int $roomId): void
    {
        Cache::forget($this->getGameDeckKey($roomId));
    }

    /**
     * Cache private cards for a player
     *
     * @param int $roomId
     * @param int $userId
     * @param array $cards
     * @return void
     */
    public function cachePrivateCards(int $roomId, int $userId, array $cards): void
    {
        Cache::put(
            $this->getPrivateCardsKey($roomId, $userId),
            $cards,
            self::PRIVATE_CARDS_TTL
        );
    }

    /**
     * Get cached private cards for a player
     *
     * @param int $roomId
     * @param int $userId
     * @return array|null
     */
    public function getPrivateCards(int $roomId, int $userId): ?array
    {
        return Cache::get($this->getPrivateCardsKey($roomId, $userId));
    }

    /**
     * Clear private cards cache for a player
     *
     * @param int $roomId
     * @param int $userId
     * @return void
     */
    public function clearPrivateCards(int $roomId, int $userId): void
    {
        Cache::forget($this->getPrivateCardsKey($roomId, $userId));
    }

    /**
     * Cache game meta data (dealer position, etc.)
     *
     * @param int $roomId
     * @param array $metaData
     * @return void
     */
    public function cacheGameMeta(int $roomId, array $metaData): void
    {
        Cache::put(
            $this->getGameMetaKey($roomId),
            $metaData,
            self::GAME_META_TTL
        );
    }

    /**
     * Get cached game meta data
     *
     * @param int $roomId
     * @return array|null
     */
    public function getGameMeta(int $roomId): ?array
    {
        return Cache::get($this->getGameMetaKey($roomId));
    }

    /**
     * Clear game meta data cache
     *
     * @param int $roomId
     * @return void
     */
    public function clearGameMeta(int $roomId): void
    {
        Cache::forget($this->getGameMetaKey($roomId));
    }

    /**
     * Clear all game-related caches for a room
     *
     * @param int $roomId
     * @return void
     */
    public function clearAllGameCaches(int $roomId): void
    {
        $this->clearGameState($roomId);
        $this->clearGameDeck($roomId);
        $this->clearGameMeta($roomId);
        
        // Clear private cards for all players (this would need to be called for each player)
        // This is a simplified approach - in a real implementation, you might want to track
        // which users have private cards for a room
    }

    /**
     * Generate cache key for game state
     *
     * @param int $roomId
     * @return string
     */
    private function getGameStateKey(int $roomId): string
    {
        return self::GAME_STATE_KEY . ':' . $roomId;
    }

    /**
     * Generate cache key for game deck
     *
     * @param int $roomId
     * @return string
     */
    private function getGameDeckKey(int $roomId): string
    {
        return self::GAME_DECK_KEY . ':' . $roomId;
    }

    /**
     * Generate cache key for private cards
     *
     * @param int $roomId
     * @param int $userId
     * @return string
     */
    private function getPrivateCardsKey(int $roomId, int $userId): string
    {
        return self::PRIVATE_CARDS_KEY . ':' . $roomId . ':' . $userId;
    }

    /**
     * Generate cache key for game meta data
     *
     * @param int $roomId
     * @return string
     */
    private function getGameMetaKey(int $roomId): string
    {
        return self::GAME_META_KEY . ':' . $roomId;
    }
}