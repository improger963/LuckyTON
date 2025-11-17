<?php

namespace App\DTOs;

class HandResultData
{
    public function __construct(
        public readonly int $rank, // Numerical rank of the hand (higher is better)
        public readonly string $name, // Name of the hand (e.g., "Flush", "Full House")
        public readonly array $cards, // The 5 cards that make up the best hand
    ) {}

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'rank' => $this->rank,
            'name' => $this->name,
            'cards' => array_map(fn($card) => $card->toArray(), $this->cards),
        ];
    }
}