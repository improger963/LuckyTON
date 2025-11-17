<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameStateUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $roomId,
        public array $gameState,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'GameStateUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'current_player' => $this->gameState['current_player_id'] ?? null,
            'pot' => $this->gameState['pot'] ?? 0,
            'community_cards' => $this->gameState['community_cards'] ?? [],
            'players' => collect($this->gameState['players_state'] ?? [])->map(function ($player) {
                return [
                    'id' => $player['id'] ?? null,
                    'chips' => $player['stack'] ?? 0,
                    'status' => $player['has_folded'] ?? false ? 'folded' : 'active',
                    'has_cards' => true, // Placeholder, don't send actual cards
                    // NOT sending actual cards or sensitive player data
                ];
            }),
        ];
    }
}