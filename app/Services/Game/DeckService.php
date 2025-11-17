<?php

namespace App\Services\Game;

use App\Enums\CardSuit;
use App\Enums\CardRank;
use App\DTOs\CardData;
use Illuminate\Support\Collection;

class DeckService
{
    /**
     * @var Collection
     */
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new Collection();
        $this->initializeDeck();
    }

    /**
     * Initialize a standard 52-card deck
     */
    private function initializeDeck(): void
    {
        $this->cards = new Collection();

        foreach (CardSuit::cases() as $suit) {
            foreach (CardRank::cases() as $rank) {
                $this->cards->push(new CardData($suit, $rank));
            }
        }
    }

    /**
     * Shuffle the deck
     */
    public function shuffle(): void
    {
        $this->cards = $this->cards->shuffle();
    }

    /**
     * Draw a card from the top of the deck
     */
    public function draw(): ?CardData
    {
        return $this->cards->pop();
    }

    /**
     * Draw multiple cards from the deck
     *
     * @param int $count Number of cards to draw
     * @return Collection
     */
    public function drawMultiple(int $count): Collection
    {
        /** @var Collection $cards */
        $cards = new Collection();

        for ($i = 0; $i < $count; $i++) {
            $card = $this->draw();
            if ($card) {
                $cards->push($card);
            }
        }

        return $cards;
    }

    /**
     * Get the number of cards remaining in the deck
     */
    public function remaining(): int
    {
        return $this->cards->count();
    }

    /**
     * Check if the deck is empty
     */
    public function isEmpty(): bool
    {
        return $this->cards->isEmpty();
    }
}