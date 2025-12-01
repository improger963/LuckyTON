<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournament extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_REGISTRATION_OPEN = 'registration_open';
    const STATUS_REGISTRATION_CLOSED = 'registration_closed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const GAME_TYPE_POKER = 'poker';
    const GAME_TYPE_BLOT = 'blot';

    protected $fillable = [
        'name',
        'description',
        'game_type',
        'prize_pool',
        'buy_in',
        'max_players',
        'registration_opens_at',
        'starts_at',
        'status',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'prize_pool' => 'decimal:2',
            'buy_in' => 'decimal:2',
            'max_players' => 'integer',
            'registration_opens_at' => 'datetime',
            'starts_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Players registered for this tournament
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tournament_players')
            ->withTimestamps();
    }

    /**
     * Check if tournament is full
     */
    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_players;
    }

    /**
     * Check if user is registered in tournament
     */
    public function hasPlayer(User $user): bool
    {
        return $this->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if tournament is joinable
     */
    public function isJoinable(): bool
    {
        return $this->status === self::STATUS_REGISTRATION_OPEN
            && $this->starts_at > now()
            && !$this->isFull();
    }

    /**
     * Get current players count
     */
    public function getCurrentPlayersCountAttribute(): int
    {
        return $this->players()->count();
    }

    /**
     * Get available spots
     */
    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->max_players - $this->current_players_count);
    }

    /**
     * Scope for available tournaments
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_REGISTRATION_OPEN)
            ->where('starts_at', '>', now());
    }

    /**
     * Scope for specific game type
     */
    public function scopeOfType($query, string $gameType)
    {
        return $query->where('game_type', $gameType);
    }

    /**
     * Scope for upcoming tournaments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope for high prize pool tournaments
     */
    public function scopeHighPrize($query, float $minPrize = 1000.0)
    {
        return $query->where('prize_pool', '>=', $minPrize);
    }

    /**
     * Scope for high buy-in tournaments
     */
    public function scopeHighBuyIn($query, float $minBuyIn = 100.0)
    {
        return $query->where('buy_in', '>=', $minBuyIn);
    }

    /**
     * Scope for tournaments with available spots
     */
    public function scopeWithAvailableSpots($query)
    {
        return $query->whereHas('players', function ($subQuery) {
            $subQuery->groupBy('tournament_id')
                    ->havingRaw('COUNT(*) < tournaments.max_players');
        });
    }

    /**
     * Scope for recently created tournaments
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}