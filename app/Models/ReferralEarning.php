<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralEarning extends Model
{
    protected $fillable = [
        'user_id',
        'referral_id',
        'amount',
        'description',
        'type',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who earned the referral (referrer)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User who was referred (the new user)
     */
    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    /**
     * Scope for completed earnings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending earnings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '+' . number_format($this->amount, 2) . ' USD';
    }

    /**
     * Check if earning is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }
}
