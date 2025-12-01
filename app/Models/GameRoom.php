<?php

// Файл: app/Models/GameRoom.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GameRoom extends Model
{
    const STATUS_WAITING = 'waiting';
    const STATUS_DISABLED = 'cancelled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'finished';

    const GAME_TYPE_POKER = 'poker';
    const GAME_TYPE_BLOT = 'blot';

    protected $fillable = [
        'game_type',
        'name',
        'description',
        'stake',
        'max_players',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAITING,
    ];

    protected function casts(): array
    {
        return [
            'stake' => 'decimal:2',
            'max_players' => 'integer',
        ];
    }

    /**
     * Players in this game room
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_room_players')
            ->withTimestamps();
    }

    /**
     * Check if room is available (waiting and not full)
     */
    public function isAvailable(): bool
    {
        return $this->isWaiting() && !$this->isFull();
    }

    /**
     * Check if room is waiting for players
     */
    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    /**
     * Check if room is full
     */
    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_players;
    }

    /**
     * Check if user is in this room
     */
    public function hasPlayer(User $user): bool
    {
        return $this->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Get current players count
     */
    public function getCurrentPlayersCountAttribute(): int
    {
        // Use loaded relationship if available, otherwise count from database
        if ($this->relationLoaded('players')) {
            return $this->players->count();
        }
        
        return $this->players()->count();
    }

    /**
     * Scope for available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /**
     * Scope for specific game type
     */
    public function scopeOfType($query, string $gameType)
    {
        return $query->where('game_type', $gameType);
    }

    /**
     * Scope for rooms with available spots
     */
    public function scopeWithAvailableSpots($query)
    {
        $maxPlayers = $this->max_players;
        return $query->where('status', self::STATUS_WAITING)
                    ->whereHas('players', function ($subQuery) use ($maxPlayers) {
                        $subQuery->groupBy('game_room_id')
                                ->havingRaw('COUNT(*) < ?', [$maxPlayers]);
                    }, '<', function ($query) {
                        $query->from('game_room_players')
                              ->selectRaw('COUNT(*)')
                              ->whereColumn('game_room_id', 'game_rooms.id');
                    });
    }

    /**
     * Scope for high stake rooms
     */
    public function scopeHighStake($query, float $minStake = 100.0)
    {
        return $query->where('stake', '>=', $minStake);
    }

    /**
     * Scope for recently created rooms
     */
    public function scopeRecent($query, int $minutes = 30)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}