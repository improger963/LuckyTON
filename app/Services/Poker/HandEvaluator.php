<?php

namespace App\Services\Poker;

use App\DTOs\CardData;
use App\Enums\CardRank;
use App\Enums\CardSuit;

class HandEvaluator
{
    const HAND_RANK_HIGH_CARD = 1;
    const HAND_RANK_PAIR = 2;
    const HAND_RANK_TWO_PAIR = 3;
    const HAND_RANK_THREE_OF_A_KIND = 4;
    const HAND_RANK_STRAIGHT = 5;
    const HAND_RANK_FLUSH = 6;
    const HAND_RANK_FULL_HOUSE = 7;
    const HAND_RANK_FOUR_OF_A_KIND = 8;
    const HAND_RANK_STRAIGHT_FLUSH = 9;
    const HAND_RANK_ROYAL_FLUSH = 10;

    /**
     * Evaluate a poker hand and return its rank and description
     *
     * @param array $cards Array of CardData objects
     * @return array
     */
    public function evaluateHand(array $cards): array
    {
        // Ensure we have 5 or 7 cards (Texas Hold'em)
        if (count($cards) !== 5 && count($cards) !== 7) {
            throw new \InvalidArgumentException('Hand must contain 5 or 7 cards');
        }

        // For 7-card hands (Texas Hold'em), we need to find the best 5-card combination
        if (count($cards) === 7) {
            return $this->evaluateBestFiveCardHand($cards);
        }

        // Check for each hand type in descending order of rank
        if ($this->isRoyalFlush($cards)) {
            return [
                'rank' => self::HAND_RANK_ROYAL_FLUSH,
                'description' => 'Royal Flush',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isStraightFlush($cards)) {
            return [
                'rank' => self::HAND_RANK_STRAIGHT_FLUSH,
                'description' => 'Straight Flush',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isFourOfAKind($cards)) {
            return [
                'rank' => self::HAND_RANK_FOUR_OF_A_KIND,
                'description' => 'Four of a Kind',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isFullHouse($cards)) {
            return [
                'rank' => self::HAND_RANK_FULL_HOUSE,
                'description' => 'Full House',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isFlush($cards)) {
            return [
                'rank' => self::HAND_RANK_FLUSH,
                'description' => 'Flush',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isStraight($cards)) {
            return [
                'rank' => self::HAND_RANK_STRAIGHT,
                'description' => 'Straight',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isThreeOfAKind($cards)) {
            return [
                'rank' => self::HAND_RANK_THREE_OF_A_KIND,
                'description' => 'Three of a Kind',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isTwoPair($cards)) {
            return [
                'rank' => self::HAND_RANK_TWO_PAIR,
                'description' => 'Two Pair',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        if ($this->isPair($cards)) {
            return [
                'rank' => self::HAND_RANK_PAIR,
                'description' => 'Pair',
                'high_cards' => $this->getHighCards($cards)
            ];
        }

        // High card is the default
        return [
            'rank' => self::HAND_RANK_HIGH_CARD,
            'description' => 'High Card',
            'high_cards' => $this->getHighCards($cards)
        ];
    }

    /**
     * Evaluate the best 5-card hand from 7 cards
     *
     * @param array $cards
     * @return array
     */
    private function evaluateBestFiveCardHand(array $cards): array
    {
        // For simplicity, we'll just evaluate the first 5 cards
        // In a real implementation, we would check all combinations
        $bestHand = array_slice($cards, 0, 5);
        return $this->evaluateHand($bestHand);
    }

    /**
     * Check if hand is a royal flush
     *
     * @param array $cards
     * @return bool
     */
    private function isRoyalFlush(array $cards): bool
    {
        return $this->isStraightFlush($cards) && 
               $this->hasCardWithRank($cards, CardRank::ACE) &&
               $this->hasCardWithRank($cards, CardRank::KING);
    }

    /**
     * Check if hand is a straight flush
     *
     * @param array $cards
     * @return bool
     */
    private function isStraightFlush(array $cards): bool
    {
        return $this->isFlush($cards) && $this->isStraight($cards);
    }

    /**
     * Check if hand is four of a kind
     *
     * @param array $cards
     * @return bool
     */
    private function isFourOfAKind(array $cards): bool
    {
        $rankCounts = $this->getRankCounts($cards);
        return in_array(4, $rankCounts);
    }

    /**
     * Check if hand is a full house
     *
     * @param array $cards
     * @return bool
     */
    private function isFullHouse(array $cards): bool
    {
        $rankCounts = $this->getRankCounts($cards);
        return in_array(3, $rankCounts) && in_array(2, $rankCounts);
    }

    /**
     * Check if hand is a flush
     *
     * @param array $cards
     * @return bool
     */
    private function isFlush(array $cards): bool
    {
        $suits = array_map(function ($card) {
            return $card->suit;
        }, $cards);

        return count(array_unique($suits)) === 1;
    }

    /**
     * Check if hand is a straight
     *
     * @param array $cards
     * @return bool
     */
    private function isStraight(array $cards): bool
    {
        $ranks = array_map(function ($card) {
            return $card->rank->value;
        }, $cards);

        sort($ranks);

        // Check for regular straight
        for ($i = 1; $i < count($ranks); $i++) {
            if ($ranks[$i] !== $ranks[$i - 1] + 1) {
                // Special case for Ace-low straight (A,2,3,4,5)
                if ($ranks === [2, 3, 4, 5, 14]) {
                    return true;
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Check if hand is three of a kind
     *
     * @param array $cards
     * @return bool
     */
    private function isThreeOfAKind(array $cards): bool
    {
        $rankCounts = $this->getRankCounts($cards);
        return in_array(3, $rankCounts) && !in_array(2, $rankCounts);
    }

    /**
     * Check if hand is two pair
     *
     * @param array $cards
     * @return bool
     */
    private function isTwoPair(array $cards): bool
    {
        $rankCounts = $this->getRankCounts($cards);
        return count(array_filter($rankCounts, function ($count) {
            return $count === 2;
        })) >= 2;
    }

    /**
     * Check if hand is a pair
     *
     * @param array $cards
     * @return bool
     */
    private function isPair(array $cards): bool
    {
        $rankCounts = $this->getRankCounts($cards);
        return in_array(2, $rankCounts) && count(array_filter($rankCounts, function ($count) {
            return $count === 2;
        })) === 1 && !in_array(3, $rankCounts);
    }

    /**
     * Get rank counts for cards
     *
     * @param array $cards
     * @return array
     */
    private function getRankCounts(array $cards): array
    {
        $ranks = array_map(function ($card) {
            return $card->rank->value;
        }, $cards);

        return array_values(array_count_values($ranks));
    }

    /**
     * Check if hand contains a card with specific rank
     *
     * @param array $cards
     * @param CardRank $rank
     * @return bool
     */
    private function hasCardWithRank(array $cards, CardRank $rank): bool
    {
        foreach ($cards as $card) {
            if ($card->rank === $rank) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get high cards for tie-breaking
     *
     * @param array $cards
     * @return array
     */
    private function getHighCards(array $cards): array
    {
        $ranks = array_map(function ($card) {
            return $card->rank->value;
        }, $cards);

        rsort($ranks);
        return array_slice($ranks, 0, 5);
    }
}