<?php

namespace App\Http\Controllers\Api;

use App\Models\GameRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GameSitRequest;
use App\Http\Requests\Api\JoinRoomRequest;
use App\Http\Requests\Api\StandUpRequest;
use App\Http\Requests\Api\LeaveRoomRequest;
use App\Http\Requests\Api\GameMoveRequest;
use App\Services\GameService;
use App\Services\GameRoomService;
use App\Services\Poker\Engine\HandlePlayerMoveService;
use App\Services\TransactionService;
use App\Exceptions\RoomFullException;
use Exception;

class GameController extends Controller
{
    /**
     * @var GameService
     */
    protected $gameService;

    /**
     * @var GameRoomService
     */
    protected $gameRoomService;

    /**
     * GameController constructor.
     *
     * @param GameService $gameService
     * @param GameRoomService $gameRoomService
     */
    public function __construct(GameService $gameService, GameRoomService $gameRoomService)
    {
        $this->gameService = $gameService;
        $this->gameRoomService = $gameRoomService;
    }

    /**
     * Get available game rooms grouped by type
     */
    public function index(): JsonResponse
    {
        try {
            $roomsData = $this->gameService->getAvailableRooms();
            return $this->successResponse($roomsData, 'Game rooms fetched successfully');
        } catch (Exception $e) {
            Log::error('Error fetching game rooms', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse('An error occurred while fetching game rooms.', 500);
        }
    }

    /**
     * Join game room
     */
    public function join(JoinRoomRequest $request, GameRoom $room): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->gameService->joinRoom($user, $room);

            return $this->successResponse($result, 'Successfully joined the game room');
        } catch (Exception $e) {
            Log::warning('Game room exception', [
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $statusCode = 500;
            if (in_array($e->getMessage(), [
                'This room is not available for joining.',
                'You are already in this room.',
                'Insufficient balance to join this room.'
            ])) {
                $statusCode = 400;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Get game room details
     */
    public function show(GameRoom $room): JsonResponse
    {
        try {
            $roomData = $this->gameService->getFormattedRoomDetails($room);
            return $this->successResponse($roomData, 'Game room details fetched successfully');
        } catch (Exception $e) {
            Log::error('Error fetching game room details', [
                'room_id' => $room->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse('An error occurred while fetching game room details.', 500);
        }
    }

    /**
     * Sit at a table in the game room
     */
    public function sit(GameSitRequest $request, GameRoom $room): JsonResponse
    {
        try {
            $user = $request->user();
            // Get seat from request, defaulting to 0 for automatic assignment if not provided
            $seat = $request->has('seat') ? $request->seat : 0;
            $result = $this->gameRoomService->takeSeat($user, $room, $seat, $request->buy_in);

            return $this->successResponse($result, 'Successfully took seat at the table');
        } catch (Exception $e) {
            Log::warning('Game room sit exception', [
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $statusCode = 500;
            if ($e instanceof RoomFullException || in_array($e->getMessage(), [
                'Invalid seat number.',
                'This seat is already occupied.',
                'Buy-in amount is below the minimum stake.',
                'You are not in this room.',
                'You are already seated at the table.',
                'Insufficient balance for this buy-in.',
                'This room is already full.',
                'No seats available in this room.',
            ])) {
                $statusCode = 400;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Handle player move
     */
    public function move(GameMoveRequest $request, GameRoom $room): JsonResponse
    {
        try {
            $user = $request->user();
            $action = $request->input('action');
            $amount = $request->input('amount', 0);
            
            // Handle player move using our poker engine
            $handleMoveService = new HandlePlayerMoveService(
                $this->gameRoomService->getGameRoomPlayerRepository(),
                $this->gameService->getWalletService(),
                new TransactionService()
            );
            
            $handleMoveService->execute($room, $user->id, $action, $amount);

            return $this->successResponse(null, 'Move processed successfully');
        } catch (Exception $e) {
            Log::warning('Game move exception', [
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'action' => $action ?? 'unknown',
                'amount' => $amount ?? 0,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $statusCode = 500;
            if (in_array($e->getMessage(), [
                'It is not your turn',
                'Player not found in game',
                'Action fold is not available',
                'Action check is not available',
                'Action call is not available',
                'Action raise is not available',
                'Cannot check when there is a bet to call',
                'Raise amount must be at least',
                'Raise amount cannot exceed your stack'
            ]) || str_starts_with($e->getMessage(), 'Action') || str_starts_with($e->getMessage(), 'Raise amount')) {
                $statusCode = 400;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Leave the game room
     */
    public function leave(LeaveRoomRequest $request, GameRoom $room): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->gameRoomService->leaveRoom($user, $room);

            return $this->successResponse($result, 'Successfully left the game room');
        } catch (Exception $e) {
            Log::warning('Game room leave exception', [
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $statusCode = 500;
            if (in_array($e->getMessage(), [
                'You are not in this room.',
            ])) {
                $statusCode = 400;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }
}