<?php

namespace App\Repositories\Eloquent;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

class WalletRepository extends \App\Repositories\BaseRepository
{
    /**
     * WalletRepository constructor.
     *
     * @param Wallet $model
     */
    public function __construct(Wallet $model)
    {
        parent::__construct($model);
    }

    /**
     * Find wallet by user ID
     *
     * @param int $userId
     * @return Wallet|null
     */
    public function findByUserId(int $userId): ?Wallet
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Find wallet by user ID with pessimistic lock
     *
     * @param int $userId
     * @return Wallet|null
     */
    public function findByUserIdWithLock(int $userId): ?Wallet
    {
        return $this->model->where('user_id', $userId)->lockForUpdate()->first();
    }

    /**
     * Get wallet with transactions
     *
     * @param int $id
     * @return Wallet|null
     */
    public function findWithTransactions(int $id): ?Wallet
    {
        return $this->model->with('transactions')->find($id);
    }

    /**
     * Get wallet by deposit address
     *
     * @param string $address
     * @return Wallet|null
     */
    public function findByDepositAddress(string $address): ?Wallet
    {
        return $this->model->where('deposit_address', $address)->first();
    }
}