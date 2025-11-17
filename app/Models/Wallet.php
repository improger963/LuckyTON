<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'deposit_address',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
        ];
    }

    /**
     * User who owns this wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Wallet transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get available balance (excluding pending withdrawals)
     */
    public function getAvailableBalanceAttribute(): float
    {
        $pendingWithdrawals = $this->transactions()
            ->pendingWithdrawals()
            ->sum('amount');

        return max(0, $this->balance - abs($pendingWithdrawals));
    }

    /**
     * Get total deposits
     */
    public function getTotalDepositsAttribute(): float
    {
        return $this->transactions()
            ->completedDeposits()
            ->sum('amount');
    }

    /**
     * Get total withdrawals
     */
    public function getTotalWithdrawalsAttribute(): float
    {
        return abs($this->transactions()
            ->completedWithdrawals()
            ->sum('amount'));
    }

    /**
     * Scope for wallets with sufficient balance
     */
    public function scopeWithSufficientBalance($query, float $amount)
    {
        return $query->where('balance', '>=', $amount);
    }
}