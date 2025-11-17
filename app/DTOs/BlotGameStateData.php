<?php

namespace App\DTOs;

use App\Enums\CardSuit;
use App\DTOs\CardData;
use Illuminate\Support\Collection;

class BlotGameStateData
{
    public function __construct(
        public readonly array $deck,
        public readonly ?CardSuit $trumpSuit,
        public readonly int $dealerId,
        public readonly int $currentPlayerId,
        public readonly array $hands,
        public readonly array $trick,
        public readonly array $scores,
        public readonly array $matchScore,
        public readonly array $announcedCombinations,
        public readonly int $round,
    ) {}

    /**
     * Convert to array for storage
     */
    public function toArray(): array
    {
        return [
            'deck' => array_map(fn($card) => $card instanceof CardData ? $card->toArray() : $card, $this->deck),
            'trump_suit' => $this->trumpSuit?->value,
            'dealer_id' => $this->dealerId,
            'current_player_id' => $this->currentPlayerId,
            'hands' => $this->hands,
            'trick' => $this->trick,
            'scores' => $this->scores,
            'match_score' => $this->matchScore,
            'announced_combinations' => $this->announcedCombinations,
            'round' => $this->round,
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            deck: $data['deck'],
            trumpSuit: isset($data['trump_suit']) ? CardSuit::from($data['trump_suit']) : null,
            dealerId: $data['dealer_id'],
            currentPlayerId: $data['current_player_id'],
            hands: $data['hands'],
            trick: $data['trick'],
            scores: $data['scores'],
            matchScore: $data['match_score'],
            announcedCombinations: $data['announced_combinations'],
            round: $data['round'],
        );
    }
}