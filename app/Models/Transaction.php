<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_GAME_STAKE = 'game_stake';
    const TYPE_GAME_WIN = 'game_win';
    const TYPE_GAME_LOSS = 'game_loss';
    const TYPE_REFERRAL_BONUS = 'referral_bonus';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'status',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    /**
     * Wallet that owns this transaction
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is a deposit
     */
    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_DEPOSIT;
    }

    /**
     * Check if transaction is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_WITHDRAWAL;
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->amount > 0 ? '+' : '';
        return $sign . number_format($this->amount, 2, '.', '');
    }

    /**
     * Get absolute amount (positive value)
     */
    public function getAbsoluteAmountAttribute(): float
    {
        return abs($this->amount);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for deposit transactions
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', self::TYPE_DEPOSIT);
    }

    /**
     * Scope for withdrawal transactions
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL);
    }

    /**
     * Scope for pending withdrawals
     */
    public function scopePendingWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL)
                    ->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed deposits
     */
    public function scopeCompletedDeposits($query)
    {
        return $query->where('type', self::TYPE_DEPOSIT)
                    ->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for completed withdrawals
     */
    public function scopeCompletedWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL)
                    ->where('status', self::STATUS_COMPLETED);
    }
}