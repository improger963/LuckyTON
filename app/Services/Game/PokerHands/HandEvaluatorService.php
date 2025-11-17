<?php

namespace App\Services\Game\PokerHands;

use App\DTOs\CardData;
use App\DTOs\HandResultData;
use App\Enums\CardRank;
use App\Enums\CardSuit;

class HandEvaluatorService
{
    // Hand rankings (higher number = better hand)
    const HIGH_CARD = 1;
    const ONE_PAIR = 2;
    const TWO_PAIR = 3;
    const THREE_OF_A_KIND = 4;
    const STRAIGHT = 5;
    const FLUSH = 6;
    const FULL_HOUSE = 7;
    const FOUR_OF_A_KIND = 8;
    const STRAIGHT_FLUSH = 9;
    const ROYAL_FLUSH = 10;

    /**
     * Evaluate the best 5-card poker hand from 7 cards
     *
     * @param CardData[] $sevenCards
     * @return HandResultData
     */
    public function evaluate(array $sevenCards): HandResultData
    {
        // Generate all possible 5-card combinations from 7 cards
        $combinations = $this->generateCombinations($sevenCards, 5);
        
        $bestHand = null;
        $bestRank = 0;
        
        foreach ($combinations as $hand) {
            $result = $this->evaluateFiveCardHand($hand);
            
            if ($result->rank > $bestRank) {
                $bestRank = $result->rank;
                $bestHand = $result;
            }
        }
        
        return $bestHand ?? new HandResultData(self::HIGH_CARD, 'High Card', []);
    }
    
    /**
     * Generate all combinations of k elements from array
     *
     * @param array $array
     * @param int $k
     * @return array
     */
    private function generateCombinations(array $array, int $k): array
    {
        $result = [];
        
        if ($k === 0) {
            return [[]];
        }
        
        if (count($array) < $k) {
            return [];
        }
        
        if (count($array) === $k) {
            return [$array];
        }
        
        $first = array_shift($array);
        $combinationsWithFirst = $this->generateCombinations($array, $k - 1);
        $combinationsWithoutFirst = $this->generateCombinations($array, $k);
        
        foreach ($combinationsWithFirst as $combination) {
            array_unshift($combination, $first);
            $result[] = $combination;
        }
        
        return array_merge($result, $combinationsWithoutFirst);
    }
    
    /**
     * Evaluate a specific 5-card hand
     *
     * @param CardData[] $hand
     * @return HandResultData
     */
    private function evaluateFiveCardHand(array $hand): HandResultData
    {
        // Sort cards by rank (highest first)
        usort($hand, function ($a, $b) {
            return $b->rank->value <=> $a->rank->value;
        });
        
        $ranks = array_map(fn($card) => $card->rank->value, $hand);
        $suits = array_map(fn($card) => $card->suit->value, $hand);
        
        // Check for flush
        $isFlush = count(array_unique($suits)) === 1;
        
        // Check for straight
        $isStraight = $this->isStraight($ranks);
        
        // Special case: Ace-low straight (A,2,3,4,5)
        $isAceLowStraight = $this->isAceLowStraight($ranks);
        
        // Handle royal flush
        if ($isFlush && $isStraight && $ranks[0] === CardRank::ACE->value) {
            return new HandResultData(self::ROYAL_FLUSH, 'Royal Flush', $hand);
        }
        
        // Handle straight flush
        if ($isFlush && ($isStraight || $isAceLowStraight)) {
            return new HandResultData(self::STRAIGHT_FLUSH, 'Straight Flush', $hand);
        }
        
        // Count rank occurrences
        $rankCounts = array_count_values($ranks);
        arsort($rankCounts); // Sort by count descending
        
        $counts = array_values($rankCounts);
        
        // Four of a kind
        if ($counts[0] === 4) {
            return new HandResultData(self::FOUR_OF_A_KIND, 'Four of a Kind', $hand);
        }
        
        // Full house
        if ($counts[0] === 3 && $counts[1] === 2) {
            return new HandResultData(self::FULL_HOUSE, 'Full House', $hand);
        }
        
        // Flush
        if ($isFlush) {
            return new HandResultData(self::FLUSH, 'Flush', $hand);
        }
        
        // Straight
        if ($isStraight || $isAceLowStraight) {
            return new HandResultData(self::STRAIGHT, 'Straight', $hand);
        }
        
        // Three of a kind
        if ($counts[0] === 3) {
            return new HandResultData(self::THREE_OF_A_KIND, 'Three of a Kind', $hand);
        }
        
        // Two pair
        if ($counts[0] === 2 && $counts[1] === 2) {
            return new HandResultData(self::TWO_PAIR, 'Two Pair', $hand);
        }
        
        // One pair
        if ($counts[0] === 2) {
            return new HandResultData(self::ONE_PAIR, 'One Pair', $hand);
        }
        
        // High card
        return new HandResultData(self::HIGH_CARD, 'High Card', $hand);
    }
    
    /**
     * Check if ranks form a straight
     *
     * @param array $ranks
     * @return bool
     */
    private function isStraight(array $ranks): bool
    {
        sort($ranks);
        
        // Check if consecutive
        for ($i = 1; $i < count($ranks); $i++) {
            if ($ranks[$i] !== $ranks[$i - 1] + 1) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check for Ace-low straight (A,2,3,4,5)
     *
     * @param array $ranks
     * @return bool
     */
    private function isAceLowStraight(array $ranks): bool
    {
        sort($ranks);
        
        // Check if it's A,2,3,4,5
        return count($ranks) === 5 && 
               in_array(CardRank::ACE->value, $ranks) &&
               in_array(2, $ranks) &&
               in_array(3, $ranks) &&
               in_array(4, $ranks) &&
               in_array(5, $ranks);
    }
}