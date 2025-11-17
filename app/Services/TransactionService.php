<?php

namespace App\Services;

use App\Repositories\Eloquent\TransactionRepository;


class TransactionService
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get all transactions for a user
     */
    public function getUserTransactions(int $userId)
    {
        return $this->transactionRepository->getUserTransactions($userId);
    }

    /**
     * Find a transaction by ID
     */
    public function findTransaction(int $transactionId)
    {
        return $this->transactionRepository->find($transactionId);
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(array $data)
    {
        return $this->transactionRepository->create($data);
    }
}