<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Eloquent\WalletRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Models\User;
use App\Models\GameRoom;
use App\Events\PotDistributed;
use App\Events\GameStateUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class WalletService
{
    /**
     * @var WalletRepository
     */
    protected $walletRepository;

    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * WalletService constructor.
     *
     * @param WalletRepository $walletRepository
     * @param TransactionRepository $transactionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        WalletRepository $walletRepository,
        TransactionRepository $transactionRepository,
        UserRepository $userRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get wallet balance with deposit address
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function getWalletBalance(User $user): array
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            throw new Exception('Wallet not found for this user.');
        }

        // Generate deposit address if not exists
        if (!$wallet->deposit_address) {
            $wallet->update([
                'deposit_address' => $this->generateDepositAddress($user)
            ]);
        }

        return [
            'balance' => (float) $wallet->balance,
            'currency' => config('platform.currency', 'USD'),
            'deposit_address' => $wallet->deposit_address,
        ];
    }

    /**
     * Get transaction history
     *
     * @param User $user
     * @param int $perPage
     * @return array
     */
    public function getTransactionHistory(User $user, int $perPage = 15): array
    {
        $wallet = $user->wallet;
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform transactions to array format
        $transformedTransactions = collect($transactions->items())->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' * $transaction->type,
                'description' => $transaction->description,
                'amount' => $transaction->formatted_amount,
                'absolute_amount' => $transaction->absolute_amount,
                'currency' => 'USD',
                'status' => $transaction->status,
                'date' => $transaction->created_at->translatedFormat('d M, H:i'),
                'timestamp' => $transaction->created_at,
                'metadata' => $transaction->metadata ?? null,
                'is_completed' => $transaction->isCompleted(),
                'is_pending' => $transaction->isPending(),
            ];
        });

        return [
            'transactions' => $transformedTransactions,
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'total_transactions' => $transactions->total(),
                'per_page' => $transactions->perPage(),
            ]
        ];
    }

    /**
     * Process withdrawal request
     *
     * @param User $user
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function processWithdrawal(User $user, array $data): array
    {
        $amount = $data['amount'];
        $address = $data['address'];
        $currency = $data['currency'];
        $pin = $data['pin'] ?? null;

        // Additional validation for PIN
        if ($user->requiresPinVerification() && !$this->validatePin($user, $pin)) {
            throw new Exception('Invalid PIN code.');
        }

        return DB::transaction(function () use ($amount, $address, $currency, $user) {
            // Log the withdrawal initiation
            AuditLogger::logFinancialAction('withdrawal_initiated', $user->id, [
                'amount' => $amount,
                'address' => $address,
                'currency' => $currency,
            ]);

            // Create withdrawal data
            $networkFee = config('wallet.network_fee', 0.1);
            $totalAmount = $amount + $networkFee;

            $wallet = $user->wallet;

            // Lock the wallet row for update to prevent race conditions
            $wallet = $this->walletRepository->find($wallet->id);

            // Check if user has sufficient balance
            if ($wallet->balance < $totalAmount) {
                AuditLogger::logFinancialAction('withdrawal_insufficient_balance', $user->id, [
                    'balance' => $wallet->balance,
                    'required' => $totalAmount,
                ]);
                
                throw new Exception('Insufficient balance.');
            }

            // Create withdrawal transaction
            $transaction = $wallet->transactions()->create([
                'type' => Transaction::TYPE_WITHDRAWAL,
                'amount' => $amount,
                'status' => Transaction::STATUS_PENDING,
                'fee' => $networkFee,
                'metadata' => [
                    'to_address' => $address,
                    'currency' => $currency,
                ],
            ]);

            // Deduct amount from wallet using DB query
            DB::table('wallets')
                ->where('id', $wallet->id)
                ->decrement('balance', $totalAmount);

            // Log successful withdrawal creation
            AuditLogger::logFinancialAction('withdrawal_created', $user->id, [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'network_fee' => $networkFee,
            ]);

            return [
                'message' => 'Withdrawal request has been created and is pending approval.',
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'network_fee' => $networkFee,
                'total_deducted' => $totalAmount,
            ];
        });
    }

    /**
     * Distribute pot to winners
     *
     * @param int $roomId
     * @param array $winners Array of winners with id and amount_won
     * @return void
     */
    public function distributePot(int $roomId, array $winners): void
    {
        // Use DB transaction for integrity
        DB::transaction(function () use ($roomId, $winners) {
            // Distribute pot to winners
            foreach ($winners as $winner) {
                $userId = $winner['id'];
                $amountWon = $winner['amount_won'];
                
                // Find the user using repository
                $user = $this->userRepository->find($userId);
                
                if ($user) {
                    // Get wallet with pessimistic lock using repository
                    $wallet = $this->walletRepository->findByUserIdWithLock($userId);
                    
                    if ($wallet) {
                        // Add winnings to balance
                        $wallet->balance += $amountWon;
                        $wallet->save();
                        
                        // Create transaction record
                        $wallet->transactions()->create([
                            'type' => 'game_win',
                            'amount' => $amountWon,
                            'description' => "Winnings from poker game in room {$roomId}",
                        ]);
                    }
                }
            }
        });
        
        // Broadcast pot distributed event
        broadcast(new PotDistributed($roomId, $winners));
        
        // Clear game state from Redis as the round is complete
        Cache::forget("game_state_{$roomId}");
        Cache::forget("game_deck_{$roomId}");
        
        // Note: In a real implementation, we would also clear private cards
        // This would require access to the GameRoom model or GameRoomPlayerRepository
        
        // Check and handle room state after pot distribution
        $this->checkAndHandleRoomStateAfterPotDistribution($roomId);
    }

    /**
     * Check and handle room state after pot distribution
     *
     * @param int $roomId
     * @return void
     */
    private function checkAndHandleRoomStateAfterPotDistribution(int $roomId): void
    {
        try {
            // Load the room
            $room = GameRoom::find($roomId);
            
            if (!$room) {
                Log::warning("Room not found when checking for waiting state", ['room_id' => $roomId]);
                return;
            }
            
            // Reload the room with the actual count of PLAYERS only
            $room->loadCount(['players as players_count' => function ($query) {
                $query->where('role', 'player');
            }]);

            // MAIN RULE: If less than 2 players and room is in progress, return to waiting state
            if ($room->players_count < 2 && $room->status === 'in_progress') {
                Log::info('Room ' . $room->id . ' has < 2 players after pot distribution. Returning to waiting state.');
                
                $room->status = 'waiting';
                $room->save();

                // Notify all that the game has ended and the room is waiting
                broadcast(new GameStateUpdated($roomId, [
                    'status' => 'waiting',
                    'players_count' => $room->players_count,
                    // Send full and current state so the frontend can reset everything
                ]));
            }
        } catch (Exception $e) {
            Log::error("Error checking room state after pot distribution", [
                'room_id' => $roomId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Deduct blind amount from player's wallet
     *
     * @param int $userId
     * @param float $amount
     * @param string $description
     * @return void
     * @throws Exception
     */
    public function deductBlind(int $userId, float $amount, string $description = 'Blind deduction'): void
    {
        // Use DB transaction for integrity
        DB::transaction(function () use ($userId, $amount, $description) {
            // Find the user using repository
            $user = $this->userRepository->find($userId);
            
            if (!$user) {
                throw new Exception("User not found: {$userId}");
            }
            
            // Get wallet with pessimistic lock using repository
            $wallet = $this->walletRepository->findByUserIdWithLock($userId);
            
            if (!$wallet) {
                throw new Exception("Wallet not found for user: {$userId}");
            }
            
            // Check if user has sufficient balance
            if ($wallet->balance < $amount) {
                throw new Exception("Insufficient balance for user: {$userId}");
            }
            
            // Deduct amount from wallet using DB query
            DB::table('wallets')
                ->where('id', $wallet->id)
                ->decrement('balance', $amount);
            
            // Create transaction record
            $wallet->transactions()->create([
                'type' => 'game_fee',
                'amount' => -$amount, // Negative amount for deductions
                'description' => $description,
            ]);
        });
    }

    /**
     * Generate deposit address (stub implementation)
     *
     * @param User $user
     * @return string
     */
    private function generateDepositAddress(User $user): string
    {
        // Generate a more secure deposit address using random bytes
        return 'ADDR_' . bin2hex(random_bytes(20)) . '_' . $user->id;
    }

    /**
     * Verify PIN code
     *
     * @param User $user
     * @param string|null $pin
     * @return bool
     */
    private function validatePin(User $user, ?string $pin): bool
    {
        if (!$user->requiresPinVerification()) {
            return true;
        }

        if (empty($pin)) {
            return false;
        }

        return Hash::check($pin, $user->pin_code);
    }
}