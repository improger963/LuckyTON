<?php

namespace App\DTOs;

class GameStateData
{
    public function __construct(
        public int $dealerPosition,
        public int $currentPlayerId,
        public float $pot,
        public array $playersState,
        public string $phase = 'preflop', // preflop, flop, turn, river, showdown
        public int $currentBet = 0,
        public array $communityCards = [],
    ) {}

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'dealer_position' => $this->dealerPosition,
            'current_player_id' => $this->currentPlayerId,
            'pot' => $this->pot,
            'players_state' => $this->playersState,
            'phase' => $this->phase,
            'current_bet' => $this->currentBet,
            'community_cards' => $this->communityCards,
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['dealer_position'],
            $data['current_player_id'],
            $data['pot'],
            $data['players_state'],
            $data['phase'] ?? 'preflop',
            $data['current_bet'] ?? 0,
            $data['community_cards'] ?? [],
        );
    }
}