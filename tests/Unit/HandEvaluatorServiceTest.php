<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Game\PokerHands\HandEvaluatorService;
use App\DTOs\CardData;
use App\Enums\CardSuit;
use App\Enums\CardRank;

class HandEvaluatorServiceTest extends TestCase
{
    /**
     * Test that the hand evaluator can identify a royal flush.
     *
     * @return void
     */
    public function test_hand_evaluator_identifies_royal_flush()
    {
        $evaluator = new HandEvaluatorService();
        
        // Create a royal flush: 10, J, Q, K, A of hearts
        $cards = [
            new CardData(CardSuit::HEARTS, CardRank::TEN),
            new CardData(CardSuit::HEARTS, CardRank::JACK),
            new CardData(CardSuit::HEARTS, CardRank::QUEEN),
            new CardData(CardSuit::HEARTS, CardRank::KING),
            new CardData(CardSuit::HEARTS, CardRank::ACE),
        ];
        
        // Add two more random cards to make it 7 cards
        $cards[] = new CardData(CardSuit::CLUBS, CardRank::TWO);
        $cards[] = new CardData(CardSuit::DIAMONDS, CardRank::THREE);
        
        $result = $evaluator->evaluate($cards);
        
        $this->assertEquals(HandEvaluatorService::ROYAL_FLUSH, $result->rank);
        $this->assertEquals('Royal Flush', $result->name);
    }
    
    /**
     * Test that the hand evaluator can identify a straight flush.
     *
     * @return void
     */
    public function test_hand_evaluator_identifies_straight_flush()
    {
        $evaluator = new HandEvaluatorService();
        
        // Create a straight flush: 5, 6, 7, 8, 9 of spades
        $cards = [
            new CardData(CardSuit::SPADES, CardRank::FIVE),
            new CardData(CardSuit::SPADES, CardRank::SIX),
            new CardData(CardSuit::SPADES, CardRank::SEVEN),
            new CardData(CardSuit::SPADES, CardRank::EIGHT),
            new CardData(CardSuit::SPADES, CardRank::NINE),
        ];
        
        // Add two more random cards to make it 7 cards
        $cards[] = new CardData(CardSuit::HEARTS, CardRank::TWO);
        $cards[] = new CardData(CardSuit::DIAMONDS, CardRank::THREE);
        
        $result = $evaluator->evaluate($cards);
        
        $this->assertEquals(HandEvaluatorService::STRAIGHT_FLUSH, $result->rank);
        $this->assertEquals('Straight Flush', $result->name);
    }
    
    /**
     * Test that the hand evaluator can identify four of a kind.
     *
     * @return void
     */
    public function test_hand_evaluator_identifies_four_of_a_kind()
    {
        $evaluator = new HandEvaluatorService();
        
        // Create four of a kind: four Aces
        $cards = [
            new CardData(CardSuit::HEARTS, CardRank::ACE),
            new CardData(CardSuit::DIAMONDS, CardRank::ACE),
            new CardData(CardSuit::CLUBS, CardRank::ACE),
            new CardData(CardSuit::SPADES, CardRank::ACE),
            new CardData(CardSuit::HEARTS, CardRank::KING),
        ];
        
        // Add two more random cards to make it 7 cards
        $cards[] = new CardData(CardSuit::CLUBS, CardRank::TWO);
        $cards[] = new CardData(CardSuit::DIAMONDS, CardRank::THREE);
        
        $result = $evaluator->evaluate($cards);
        
        $this->assertEquals(HandEvaluatorService::FOUR_OF_A_KIND, $result->rank);
        $this->assertEquals('Four of a Kind', $result->name);
    }
    
    /**
     * Test that the hand evaluator can identify a full house.
     *
     * @return void
     */
    public function test_hand_evaluator_identifies_full_house()
    {
        $evaluator = new HandEvaluatorService();
        
        // Create a full house: three Kings and two 5s
        $cards = [
            new CardData(CardSuit::HEARTS, CardRank::KING),
            new CardData(CardSuit::DIAMONDS, CardRank::KING),
            new CardData(CardSuit::CLUBS, CardRank::KING),
            new CardData(CardSuit::SPADES, CardRank::FIVE),
            new CardData(CardSuit::HEARTS, CardRank::FIVE),
        ];
        
        // Add two more random cards to make it 7 cards
        $cards[] = new CardData(CardSuit::CLUBS, CardRank::TWO);
        $cards[] = new CardData(CardSuit::DIAMONDS, CardRank::THREE);
        
        $result = $evaluator->evaluate($cards);
        
        $this->assertEquals(HandEvaluatorService::FULL_HOUSE, $result->rank);
        $this->assertEquals('Full House', $result->name);
    }
}