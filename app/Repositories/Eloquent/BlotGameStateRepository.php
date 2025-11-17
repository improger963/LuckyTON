<?php

namespace App\Repositories\Eloquent;

use App\DTOs\BlotGameStateData;
use Illuminate\Support\Facades\Cache;

class BlotGameStateRepository
{
    /**
     * Get game state by room ID
     *
     * @param int $roomId
     * @return BlotGameStateData|null
     */
    public function getGameState(int $roomId): ?BlotGameStateData
    {
        $stateArray = Cache::get("blot_state_{$roomId}");
        
        if (!$stateArray) {
            return null;
        }
        
        return BlotGameStateData::fromArray($stateArray);
    }
    
    /**
     * Save game state
     *
     * @param int $roomId
     * @param BlotGameStateData $gameState
     * @return void
     */
    public function saveGameState(int $roomId, BlotGameStateData $gameState): void
    {
        Cache::put("blot_state_{$roomId}", $gameState->toArray(), now()->addHours(1));
    }
    
    /**
     * Delete game state
     *
     * @param int $roomId
     * @return void
     */
    public function deleteGameState(int $roomId): void
    {
        Cache::forget("blot_state_{$roomId}");
    }
    
    /**
     * Get match score by room ID
     *
     * @param int $roomId
     * @return array
     */
    public function getMatchScore(int $roomId): array
    {
        return Cache::get("blot_match_score_{$roomId}", []);
    }
    
    /**
     * Save match score
     *
     * @param int $roomId
     * @param array $matchScore
     * @return void
     */
    public function saveMatchScore(int $roomId, array $matchScore): void
    {
        Cache::put("blot_match_score_{$roomId}", $matchScore, now()->addHours(1));
    }
}