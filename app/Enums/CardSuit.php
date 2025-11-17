<?php

namespace App\Enums;

enum CardSuit: string
{
    case HEARTS = 'hearts';
    case DIAMONDS = 'diamonds';
    case CLUBS = 'clubs';
    case SPADES = 'spades';

    /**
     * Get the symbol for the suit
     */
    public function symbol(): string
    {
        return match($this) {
            self::HEARTS => '♥',
            self::DIAMONDS => '♦',
            self::CLUBS => '♣',
            self::SPADES => '♠',
        };
    }

    /**
     * Check if the suit is red
     */
    public function isRed(): bool
    {
        return $this === self::HEARTS || $this === self::DIAMONDS;
    }

    /**
     * Check if the suit is black
     */
    public function isBlack(): bool
    {
        return $this === self::CLUBS || $this === self::SPADES;
    }
}