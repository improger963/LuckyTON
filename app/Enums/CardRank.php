<?php

namespace App\Enums;

enum CardRank: int
{
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;
    case SIX = 6;
    case SEVEN = 7;
    case EIGHT = 8;
    case NINE = 9;
    case TEN = 10;
    case JACK = 11;
    case QUEEN = 12;
    case KING = 13;
    case ACE = 14;

    /**
     * Get the display value for the rank
     */
    public function displayValue(): string
    {
        return match($this) {
            self::JACK => 'J',
            self::QUEEN => 'Q',
            self::KING => 'K',
            self::ACE => 'A',
            default => (string) $this->value,
        };
    }

    /**
     * Get the full name of the rank
     */
    public function fullName(): string
    {
        return match($this) {
            self::TWO => 'Two',
            self::THREE => 'Three',
            self::FOUR => 'Four',
            self::FIVE => 'Five',
            self::SIX => 'Six',
            self::SEVEN => 'Seven',
            self::EIGHT => 'Eight',
            self::NINE => 'Nine',
            self::TEN => 'Ten',
            self::JACK => 'Jack',
            self::QUEEN => 'Queen',
            self::KING => 'King',
            self::ACE => 'Ace',
        };
    }
}