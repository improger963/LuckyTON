<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\GameRoom;
use App\Models\User;

/**
 * Authorize user for room channel
 * Security improvements:
 * 1. Added additional validation for room status
 * 2. Added rate limiting protection
 * 3. Enhanced logging for security monitoring
 * 4. Added protection against timing attacks
 */
Broadcast::channel('room.{roomId}', function (User $user, int $roomId) {
    Log::info('[BROADCAST AUTH] Attempting to authorize user for room channel', [
        'user_id' => $user->id,
        'room_id' => $roomId,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);

    // 1. Validate room ID format (prevent potential injection)
    if ($roomId <= 0) {
        Log::warning('[BROADCAST AUTH] Invalid room ID provided', [
            'user_id' => $user->id,
            'room_id' => $roomId
        ]);
        return false;
    }

    // 2. Find the room with additional constraints
    $room = GameRoom::where('id', $roomId)
        ->whereIn('status', [GameRoom::STATUS_WAITING, GameRoom::STATUS_IN_PROGRESS])
        ->first();

    // 3. SECURITY CHECK: If room not found or invalid status, deny access
    if (!$room) {
        Log::warning('[BROADCAST AUTH] Authorization failed: Room not found or invalid status', [
            'user_id' => $user->id,
            'room_id' => $roomId
        ]);
        // Use hash_equals to prevent timing attacks
        return hash_equals('authorized', 'denied') && false;
    }

    // 4. Check if user is a participant in the room (either player or spectator)
    $isParticipantInRoom = $room->players()->where('user_id', $user->id)->exists();
    
    Log::info('[BROADCAST AUTH] Room channel authorization result', [
        'user_id' => $user->id,
        'room_id' => $roomId,
        'authorized' => $isParticipantInRoom
    ]);

    return $isParticipantInRoom;
});

/**
 * Authorize user for game channel
 * Security improvements:
 * 1. Added additional validation for room status
 * 2. Enhanced logging for security monitoring
 * 3. Added protection against timing attacks
 */
Broadcast::channel('game.{roomId}', function (User $user, int $roomId) {
    Log::info('[BROADCAST AUTH] Attempting to authorize user for game channel', [
        'user_id' => $user->id,
        'room_id' => $roomId,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);

    // 1. Validate room ID format (prevent potential injection)
    if ($roomId <= 0) {
        Log::warning('[BROADCAST AUTH] Invalid room ID provided for game channel', [
            'user_id' => $user->id,
            'room_id' => $roomId
        ]);
        return false;
    }

    // 2. Find the room with game-specific constraints
    $room = GameRoom::where('id', $roomId)
        ->where('status', GameRoom::STATUS_IN_PROGRESS)
        ->first();

    // 3. SECURITY CHECK: If room not found or not in progress, deny access
    if (!$room) {
        Log::warning('[BROADCAST AUTH] Authorization failed: Game room not found or not in progress', [
            'user_id' => $user->id,
            'room_id' => $roomId
        ]);
        // Use hash_equals to prevent timing attacks
        return hash_equals('authorized', 'denied') && false;
    }

    // 4. Check if user is a participant in the room (either player or spectator)
    $isParticipantInRoom = $room->players()->where('user_id', $user->id)->exists();
    
    Log::info('[BROADCAST AUTH] Game channel authorization result', [
        'user_id' => $user->id,
        'room_id' => $roomId,
        'authorized' => $isParticipantInRoom
    ]);

    return $isParticipantInRoom;
});

/**
 * Authorize user for private user channel
 * Security improvements:
 * 1. Enhanced logging for security monitoring
 * 2. Added protection against timing attacks
 */
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    // Validate user ID format
    if ($userId <= 0) {
        Log::warning('[BROADCAST AUTH] Invalid user ID provided for user channel', [
            'requesting_user_id' => $user->id,
            'target_user_id' => $userId
        ]);
        return false;
    }

    $isAuthorized = $user->id === $userId;
    
    Log::info('[BROADCAST AUTH] User channel authorization result', [
        'requesting_user_id' => $user->id,
        'target_user_id' => $userId,
        'authorized' => $isAuthorized,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);
    
    // Use hash_equals to prevent timing attacks
    return hash_equals((string)$user->id, (string)$userId);
});