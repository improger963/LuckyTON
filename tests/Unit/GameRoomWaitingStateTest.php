<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\GameRoom;
use App\Services\WalletService;
use App\Services\GameRoomService;
use App\Events\GameStateUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Jobs\StartNewGameRound;
use Database\Seeders\TestDataSeeder;

class GameRoomWaitingStateTest extends TestCase
{
    protected User $user1;
    protected User $user2;
    protected GameRoom $room;
    protected GameRoomService $gameRoomService;
    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users
        $this->user1 = User::factory()->create([
            'username' => 'player1',
        ]);
        
        $this->user2 = User::factory()->create([
            'username' => 'player2',
        ]);
        
        // Create a game room
        $this->room = GameRoom::factory()->create([
            'name' => 'Test Room',
            'game_type' => 'poker',
            'stake' => 100,
            'max_players' => 6,
            'status' => 'waiting'
        ]);
        
        // Initialize services
        $this->gameRoomService = app(GameRoomService::class);
        $this->walletService = app(WalletService::class);
    }

    public function test_room_returns_to_waiting_state_when_player_leaves()
    {
        Event::fake();
        
        // Add both players to the room
        $this->room->players()->attach($this->user1->id, ['role' => 'player']);
        $this->room->players()->attach($this->user2->id, ['role' => 'player']);
        
        // Set room to in_progress
        $this->room->status = 'in_progress';
        $this->room->save();
        
        // Verify room is in progress
        $this->assertEquals('in_progress', $this->room->fresh()->status);
        
        // Player 2 leaves
        $this->gameRoomService->leaveRoom($this->user2, $this->room);
        
        // Verify room is now waiting
        $this->assertEquals('waiting', $this->room->fresh()->status);
        
        // Verify GameStateUpdated event was broadcast
        Event::assertDispatched(GameStateUpdated::class);
    }

    public function test_room_returns_to_waiting_state_after_pot_distribution()
    {
        Event::fake();
        
        // Add both players to the room
        $this->room->players()->attach($this->user1->id, ['role' => 'player']);
        $this->room->players()->attach($this->user2->id, ['role' => 'player']);
        
        // Set room to in_progress
        $this->room->status = 'in_progress';
        $this->room->save();
        
        // Verify room is in progress
        $this->assertEquals('in_progress', $this->room->fresh()->status);
        
        // Distribute pot with only one winner (simulating one player leaving during game)
        $winners = [
            ['id' => $this->user1->id, 'amount_won' => 200]
        ];
        
        // Remove one player to simulate them leaving during the game
        $this->room->players()->detach($this->user2->id);
        
        // Distribute the pot
        $this->walletService->distributePot($this->room->id, $winners);
        
        // Verify room is now waiting
        $this->assertEquals('waiting', $this->room->fresh()->status);
        
        // Verify GameStateUpdated event was broadcast
        Event::assertDispatched(GameStateUpdated::class);
    }

    public function test_start_new_game_round_returns_to_waiting_when_not_enough_players()
    {
        Event::fake();
        Queue::fake();
        
        // Add only one player to the room
        $this->room->players()->attach($this->user1->id, ['role' => 'player']);
        
        // Set room to in_progress
        $this->room->status = 'in_progress';
        $this->room->save();
        
        // Dispatch the StartNewGameRound job
        $job = new StartNewGameRound($this->room);
        $job->handle(app('App\Services\GameService'), app('App\Repositories\Eloquent\GameRoomPlayerRepository'));
        
        // Verify room is now waiting
        $this->assertEquals('waiting', $this->room->fresh()->status);
        
        // Verify GameStateUpdated event was broadcast
        Event::assertDispatched(GameStateUpdated::class);
    }
}