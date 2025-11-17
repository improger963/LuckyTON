<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DTOs\GameStateData;

class GameStateDataTest extends TestCase
{
    /**
     * Test that GameStateData can be created and converted to array.
     *
     * @return void
     */
    public function test_game_state_data_can_be_created_and_converted_to_array()
    {
        // Create players state
        $playersState = [
            [
                'id' => 1,
                'username' => 'player1',
                'stack' => 1000,
                'current_bet' => 50,
                'has_folded' => false,
                'is_all_in' => false,
            ],
            [
                'id' => 2,
                'username' => 'player2',
                'stack' => 950,
                'current_bet' => 50,
                'has_folded' => false,
                'is_all_in' => false,
            ]
        ];
        
        // Create game state data
        $gameState = new GameStateData(
            dealerPosition: 0,
            currentPlayerId: 1,
            pot: 100,
            playersState: $playersState,
            phase: 'preflop',
            currentBet: 50,
            communityCards: []
        );
        
        // Convert to array
        $array = $gameState->toArray();
        
        // Assert that the data is correct
        $this->assertEquals(0, $array['dealer_position']);
        $this->assertEquals(1, $array['current_player_id']);
        $this->assertEquals(100, $array['pot']);
        $this->assertEquals($playersState, $array['players_state']);
        $this->assertEquals('preflop', $array['phase']);
        $this->assertEquals(50, $array['current_bet']);
        $this->assertEquals([], $array['community_cards']);
    }
    
    /**
     * Test that GameStateData can be created from array.
     *
     * @return void
     */
    public function test_game_state_data_can_be_created_from_array()
    {
        // Create array data
        $array = [
            'dealer_position' => 0,
            'current_player_id' => 1,
            'pot' => 100,
            'players_state' => [
                [
                    'id' => 1,
                    'username' => 'player1',
                    'stack' => 1000,
                    'current_bet' => 50,
                    'has_folded' => false,
                    'is_all_in' => false,
                ],
                [
                    'id' => 2,
                    'username' => 'player2',
                    'stack' => 950,
                    'current_bet' => 50,
                    'has_folded' => false,
                    'is_all_in' => false,
                ]
            ],
            'phase' => 'preflop',
            'current_bet' => 50,
            'community_cards' => [],
        ];
        
        // Create game state data from array
        $gameState = GameStateData::fromArray($array);
        
        // Assert that the data is correct
        $this->assertEquals(0, $gameState->dealerPosition);
        $this->assertEquals(1, $gameState->currentPlayerId);
        $this->assertEquals(100, $gameState->pot);
        $this->assertEquals($array['players_state'], $gameState->playersState);
        $this->assertEquals('preflop', $gameState->phase);
        $this->assertEquals(50, $gameState->currentBet);
        $this->assertEquals([], $gameState->communityCards);
    }
}