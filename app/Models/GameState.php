<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GameState extends Model
{
    protected $fillable = [
        'room_id',
        'state_data',
        'players',
    ];

    protected $casts = [
        'state_data' => 'array',
        'players' => 'array'
    ];

    /**
     * Game room this state belongs to
     */
    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'room_id');
    }

    /**
     * Save game state to both database and cache
     *
     * @param int $roomId
     * @param array $state
     * @return void
     */
    public static function saveState($roomId, $state)
    {
        // Save to database
        self::updateOrCreate(
            ['room_id' => $roomId],
            [
                'state_data' => $state,
                'players' => $state['players'] ?? [],
                'updated_at' => now()
            ]
        );

        // Save to cache for fast access
        Cache::put("game_state:{$roomId}", $state, 3600);
    }

    /**
     * Load game state from cache or database
     *
     * @param int $roomId
     * @return array|null
     */
    public static function loadState($roomId)
    {
        // Try to get from cache first
        $state = Cache::get("game_state:{$roomId}");

        if ($state) {
            return $state;
        }

        // If not in cache, get from database
        $gameState = self::where('room_id', $roomId)->first();

        if ($gameState) {
            // Save to cache for next time
            Cache::put("game_state:{$roomId}", $gameState->state_data, 3600);
            return $gameState->state_data;
        }

        return null;
    }

    /**
     * Delete game state
     *
     * @param int $roomId
     * @return void
     */
    public static function deleteState($roomId)
    {
        // Delete from database
        self::where('room_id', $roomId)->delete();

        // Delete from cache
        Cache::forget("game_state:{$roomId}");
    }
}