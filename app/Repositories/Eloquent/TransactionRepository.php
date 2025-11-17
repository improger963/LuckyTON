<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\BaseRepository;

class TransactionRepository extends BaseRepository
{
    /**
     * TransactionRepository constructor.
     *
     * @param Transaction $model
     */
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all transactions for a user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTransactions(int $userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

    /**
     * Get pending withdrawals
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingWithdrawals()
    {
        return $this->model->pendingWithdrawals()->get();
    }

    /**
     * Get completed deposits
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompletedDeposits()
    {
        return $this->model->completedDeposits()->get();
    }

    /**
     * Get completed withdrawals
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompletedWithdrawals()
    {
        return $this->model->completedWithdrawals()->get();
    }

    /**
     * Find transaction by wallet ID and status
     *
     * @param int $walletId
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByWalletIdAndStatus(int $walletId, string $status)
    {
        return $this->model->where('wallet_id', $walletId)
            ->where('status', $status)
            ->get();
    }
}