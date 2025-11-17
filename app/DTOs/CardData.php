<?php

namespace App\DTOs;

use App\Enums\CardSuit;
use App\Enums\CardRank;

class CardData
{
    public function __construct(
        public readonly CardSuit $suit,
        public readonly CardRank $rank,
    ) {}

    /**
     * Get the display string for the card
     */
    public function display(): string
    {
        return $this->rank->displayValue() . $this->suit->symbol();
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'suit' => $this->suit->value,
            'rank' => $this->rank->value,
            'display' => $this->display(),
            'suit_symbol' => $this->suit->symbol(),
            'rank_display' => $this->rank->displayValue(),
        ];
    }
}