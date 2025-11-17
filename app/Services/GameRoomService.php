<?php

namespace App\Services;

use App\Repositories\Eloquent\GameRoomRepository;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Services\WalletService;
use App\Services\GameService;
use App\Models\User;
use App\Models\GameRoom;
use App\Events\PlayerSatDown;
use App\Events\PlayerStoodUp;
use App\Events\PlayerLeftRoom;
use App\Events\GameStarted;
use App\Events\GameStateUpdated;
use App\Exceptions\RoomFullException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GameRoomService
{
    /**
     * @var GameRoomRepository
     */
    protected $gameRoomRepository;

    /**
     * @var GameRoomPlayerRepository
     */
    protected $gameRoomPlayerRepository;

    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * @var GameService
     */
    protected $gameService;

    /**
     * GameRoomService constructor.
     *
     * @param GameRoomRepository $gameRoomRepository
     * @param GameRoomPlayerRepository $gameRoomPlayerRepository
     * @param WalletService $walletService
     * @param GameService $gameService
     */
    public function __construct(
        GameRoomRepository $gameRoomRepository,
        GameRoomPlayerRepository $gameRoomPlayerRepository,
        WalletService $walletService,
        GameService $gameService
    ) {
        $this->gameRoomRepository = $gameRoomRepository;
        $this->gameRoomPlayerRepository = $gameRoomPlayerRepository;
        $this->walletService = $walletService;
        $this->gameService = $gameService;
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

    /**
     * Join room as spectator
     *
     * @param User $user
     * @param GameRoom $room
     * @return array
     * @throws Exception
     */
    public function joinAsSpectator(User $user, GameRoom $room): array
    {
        // Check if room is available
        if (!$room->isAvailable()) {
            throw new Exception('This room is not available for joining.');
        }

        // Check if user is already in the room - if so, just return success (idempotent behavior)
        if ($this->gameRoomPlayerRepository->findPlayer($room, $user)) {
            // Get available seats
            $availableSeats = $this->gameRoomPlayerRepository->getAvailableSeats($room);

            return [
                'message' => 'You have successfully joined the room as a spectator.',
                'role' => 'spectator',
                'available_seats' => $availableSeats,
                'max_players' => $room->max_players,
            ];
        }

        // Add user as spectator
        $this->gameRoomPlayerRepository->addSpectator($room, $user);

        // Get available seats
        $availableSeats = $this->gameRoomPlayerRepository->getAvailableSeats($room);

        return [
            'message' => 'You have successfully joined the room as a spectator.',
            'role' => 'spectator',
            'available_seats' => $availableSeats,
            'max_players' => $room->max_players,
        ];
    }

    /**
     * Take a seat at the table (with atomic operations and pessimistic locking)
     *
     * @param User $user
     * @param GameRoom $room
     * @param int $seat
     * @param float $buyIn
     * @return array
     * @throws Exception
     */
    public function takeSeat(User $user, GameRoom $room, int $seat, float $buyIn): array
    {
        return DB::transaction(function () use ($user, $room, $seat, $buyIn) {
            // Lock the room for update to prevent race conditions
            /** @var GameRoom|null $lockedRoom */
            $lockedRoom = GameRoom::where('id', $room->id)->lockForUpdate()->first();
            
            // Validate room exists
            if (!$lockedRoom) {
                throw new Exception('Room not found.');
            }
            
            // Ensure lockedRoom is a proper GameRoom model instance
            if (!($lockedRoom instanceof \App\Models\GameRoom)) {
                $lockedRoom = GameRoom::find($lockedRoom->id);
                if (!$lockedRoom) {
                    throw new Exception('Room not found.');
                }
            }
            
            // Check if room is full by comparing player count with max players
            $currentPlayerCount = $this->gameRoomPlayerRepository->getPlayerCount($lockedRoom);
            if ($currentPlayerCount >= $lockedRoom->max_players) {
                throw new RoomFullException('This room is already full.');
            }
            
            // Get occupied seats
            $occupiedSeats = $this->gameRoomPlayerRepository->getOccupiedSeats($lockedRoom);
            
            // If seat parameter is 0 or not provided, automatically assign the first available seat
            if ($seat === 0) {
                // Find first available seat (1 to max_players)
                $availableSeat = null;
                for ($i = 1; $i <= $lockedRoom->max_players; $i++) {
                    if (!in_array($i, $occupiedSeats)) {
                        $availableSeat = $i;
                        break;
                    }
                }
                
                // If no seat is available, throw an exception
                if ($availableSeat === null) {
                    throw new RoomFullException('No seats available in this room.');
                }
                
                $seat = $availableSeat;
            } else {
                // Validate seat number
                if ($seat < 1 || $seat > $lockedRoom->max_players) {
                    throw new Exception('Invalid seat number.');
                }
                
                // Check if seat is occupied
                if (in_array($seat, $occupiedSeats)) {
                    throw new Exception('This seat is already occupied.');
                }
            }

            // Check buy-in limits
            if ($buyIn < $lockedRoom->stake) {
                throw new Exception('Buy-in amount is below the minimum stake.');
            }

            // Check if user is in the room
            $playerRecord = $this->gameRoomPlayerRepository->findPlayer($lockedRoom, $user);
            if (!$playerRecord) {
                throw new Exception('You are not in this room.');
            }

            // Check if user is already a player
            if (isset($playerRecord->role) && $playerRecord->role === 'player') {
                throw new Exception('You are already seated at the table.');
            }

            // Check user balance using pessimistic locking on wallet
            /** @var \App\Models\Wallet|null $lockedWallet */
            $lockedWallet = $user->wallet()->lockForUpdate()->first();
            if ($lockedWallet && $lockedWallet->balance < $buyIn) {
                throw new Exception('Insufficient balance for this buy-in.');
            }

            // Process payment through wallet service
            if ($lockedWallet) {
                // Use DB query to update balance instead of calling protected method directly
                \Illuminate\Support\Facades\DB::table('wallets')
                    ->where('id', $lockedWallet->id)
                    ->decrement('balance', $buyIn);
            }

            // Update user to player role with assigned seat
            $this->gameRoomPlayerRepository->updateToPlayer($lockedRoom, $user, $seat, $buyIn);

            // Broadcast event
            broadcast(new PlayerSatDown($user, $lockedRoom, $seat, $buyIn))->toOthers();

            // --- АГРЕССИВНОЕ ЛОГИРОВАНИЕ ДЛЯ ОТЛАДКИ БАГА №4 ---
            // Перезагружаем комнату с актуальным количеством ИМЕННО ИГРОКОВ
            // Ensure lockedRoom is a proper GameRoom model instance
            if (!($lockedRoom instanceof \App\Models\GameRoom)) {
                $lockedRoom = GameRoom::find($lockedRoom->id);
                if (!$lockedRoom) {
                    throw new Exception('Room not found.');
                }
            }
            $lockedRoom->loadCount(['players as players_count' => function ($query) {
                $query->where('role', 'player');
            }]);

            // Получаем детальную информацию для логирования
            $playerCount = $lockedRoom->players_count;
            $roomStatus = $lockedRoom->status;
            $conditionResult = ($playerCount >= 2 && $roomStatus === 'waiting');
            
            // Логируем критическую информацию
            \Illuminate\Support\Facades\Log::critical('[GAME START DEBUG] Game start condition check', [
                'roomId' => $lockedRoom->id,
                'players_count' => $playerCount,
                'players_count_type' => gettype($playerCount),
                'room_status' => $roomStatus,
                'room_status_type' => gettype($roomStatus),
                'condition_players_check' => $playerCount >= 2,
                'condition_status_check' => $roomStatus === 'waiting',
                'condition_result' => $conditionResult
            ]);
            
            // Также логируем всех игроков в комнате для дополнительной отладки
            $allPlayers = $this->gameRoomPlayerRepository->getParticipants($lockedRoom);
            $playerDetails = [];
            foreach ($allPlayers as $player) {
                $playerDetails[] = [
                    'user_id' => $player->user_id ?? null,
                    'role' => $player->role ?? 'unknown',
                    'seat' => $player->seat ?? null
                ];
            }
            \Illuminate\Support\Facades\Log::critical('[GAME START DEBUG] All players in room', [
                'roomId' => $lockedRoom->id,
                'players' => $playerDetails
            ]);
            
            // Логируем результат подсчета игроков через репозиторий
            $repositoryPlayerCount = $this->gameRoomPlayerRepository->getPlayerCount($lockedRoom);
            \Illuminate\Support\Facades\Log::critical('[GAME START DEBUG] Repository player count', [
                'roomId' => $lockedRoom->id,
                'repository_count' => $repositoryPlayerCount
            ]);
            // --- КОНЕЦ АГРЕССИВНОГО ЛОГИРОВАНИЯ ---

            // Check if room has at least 2 players and start the game automatically
            if ($playerCount >= 2 && $roomStatus === 'waiting') {
                \Illuminate\Support\Facades\Log::critical('[GAME START TRIGGERED] Conditions met, starting game for room', [
                    'roomId' => $lockedRoom->id
                ]);
                
                // 1. First change the room status to in_progress
                $lockedRoom->status = 'in_progress';
                $this->gameRoomRepository->update($lockedRoom, ['status' => 'in_progress']); // Save through repository
                
                // Refresh the room object to get updated status
                $lockedRoom = $this->gameRoomRepository->find($lockedRoom->id);

                // 2. Broadcast that the game has started
                broadcast(new GameStarted($lockedRoom));

                // 3. Immediately start the first hand deal
                // This ensures the full cycle of the first deal is executed right after the game starts
                \Illuminate\Support\Facades\Log::critical('[GAME START] Starting poker game for room', [
                    'roomId' => $lockedRoom->id
                ]);
                $this->gameService->startPokerGame($lockedRoom);
            }

            return [
                'message' => 'You have successfully taken a seat at the table.',
                'role' => 'player',
                'seat' => $seat,
                'stack' => $buyIn,
            ];
        });
    }

    /**
     * Stand up from the table (become spectator again)
     *
     * @param User $user
     * @param GameRoom $room
     * @return array
     * @throws Exception
     */
    public function standUp(User $user, GameRoom $room): array
    {
        // Check if user is in the room
        $playerRecord = $this->gameRoomPlayerRepository->findPlayer($room, $user);
        if (!$playerRecord) {
            throw new Exception('You are not in this room.');
        }

        // Check if user is a player
        if (!isset($playerRecord->role) || $playerRecord->role !== 'player') {
            throw new Exception('You are not seated at the table.');
        }

        // Check if player is in the middle of a hand (simplified check)
        // In a real implementation, this would check game state
        // For now, we'll assume they can stand up

        // Return stack to user's wallet
        if (isset($playerRecord->stack)) {
            // Use DB query to update balance instead of calling protected method directly
            \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $user->id)
                ->increment('balance', $playerRecord->stack);
        }

        // Update user to spectator role
        $this->gameRoomPlayerRepository->updateToSpectator($room, $user);

        // Broadcast event
        broadcast(new PlayerStoodUp($user, $room))->toOthers();

        // Check and correct room state
        $this->checkAndCorrectRoomState($room);

        return [
            'message' => 'You have successfully stood up from the table.',
            'role' => 'spectator',
            'returned_amount' => isset($playerRecord->stack) ? $playerRecord->stack : 0,
        ];
    }

    /**
     * Leave the room completely
     *
     * @param User $user
     * @param GameRoom $room
     * @return array
     * @throws Exception
     */
    public function leaveRoom(User $user, GameRoom $room): array
    {
        // Check if user is in the room
        $playerRecord = $this->gameRoomPlayerRepository->findPlayer($room, $user);
        if (!$playerRecord) {
            throw new Exception('You are not in this room.');
        }

        // If user is a player, return their stack
        if (isset($playerRecord->role) && $playerRecord->role === 'player') {
            // Return stack to user's wallet
            if (isset($playerRecord->stack)) {
                // Use DB query to update balance instead of calling protected method directly
                DB::table('wallets')
                    ->where('user_id', $user->id)
                    ->increment('balance', $playerRecord->stack);
            }
            
            // Broadcast event
            broadcast(new PlayerStoodUp($user, $room))->toOthers();
        }

        // Remove user from room
        $this->gameRoomPlayerRepository->remove($room, $user);

        // Broadcast PlayerLeftRoom event
        broadcast(new PlayerLeftRoom($user, $room))->toOthers();

        // Check and correct room state
        $this->checkAndCorrectRoomState($room);

        return [
            'message' => 'You have successfully left the room.',
        ];
    }

    /**
     * Check and correct room state based on player count
     *
     * @param GameRoom $room
     * @return GameRoom
     */
    public function checkAndCorrectRoomState(GameRoom $room): GameRoom
    {
        // Reload the actual count of PLAYERS only
        $playerCount = $this->gameRoomPlayerRepository->getPlayerCount($room);

        // MAIN RULE: If less than 2 players and room is in progress, force status to waiting
        if ($playerCount < 2 && $room->status === 'in_progress') {
            Log::info('Room ' . $room->id . ' has < 2 players. Forcing status to "waiting".');
            
            // Update status through repository
            $updateResult = $this->gameRoomRepository->update($room, ['status' => 'waiting']);
            
            // Refresh the room object to get updated status
            $room = $this->gameRoomRepository->find($room->id);

            // Notify all that the game has ended and the room is waiting
            broadcast(new GameStateUpdated($room->id, [
                'status' => 'waiting',
                'players_count' => $playerCount,
            ]));
        }

        return $room;
    }

    /**
     * Get room details with state correction
     *
     * @param int $roomId
     * @return GameRoom
     */
    public function getRoomDetails(int $roomId): GameRoom
    {
        $room = $this->gameRoomRepository->find($roomId);
        
        // "Sanitary" check before returning data to client
        $room = $this->checkAndCorrectRoomState($room);
        
        return $room;
    }

    /**
     * Get room participants with roles
     *
     * @param GameRoom $room
     * @return array
     */
    public function getRoomParticipants(GameRoom $room): array
    {
        $participants = $this->gameRoomPlayerRepository->getParticipants($room);
        
        $players = [];
        $spectators = [];
        
        foreach ($participants as $participant) {
            /** @var object $participant */
            if (isset($participant->role) && $participant->role === 'player') {
                $players[] = [
                    
                    'user_id' => $participant->user_id,
                    'seat' => isset($participant->seat) ? $participant->seat : null,
                    'stack' => isset($participant->stack) ? $participant->stack : 0,
                ];
            } else {
                $spectators[] = [
                    'user_id' => $participant->user_id,
                ];
            }
        }
        
        return [
            'players' => $players,
            'spectators' => $spectators,
        ];
    }
}