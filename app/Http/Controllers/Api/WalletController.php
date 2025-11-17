<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalletWithdrawalRequest;
use App\Http\Requests\Api\CreateDepositRequest;
use App\Services\WalletService;
use App\Services\AuditLogger;
use App\Models\User;
use Exception;

class WalletController extends Controller
{
    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * WalletController constructor.
     *
     * @param WalletService $walletService
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get wallet balance with deposit address
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $walletData = $this->walletService->getWalletBalance($user);

            return $this->successResponse($walletData, 'Wallet balance fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching wallet information.', 500);
        }
    }

    /**
     * Get transaction history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $historyData = $this->walletService->getTransactionHistory($user);

            return $this->successResponse($historyData, 'Transaction history fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching transaction history.', 500);
        }
    }

    /**
     * Process withdrawal request
     */
    public function withdraw(WalletWithdrawalRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->walletService->processWithdrawal($user, $request->validated());

            return $this->successResponse($result, 'Withdrawal request has been created and is pending approval.', 202);
        } catch (Exception $e) {
            AuditLogger::logSecurityEvent('withdrawal_failed', 'error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $statusCode = 400;
            if ($e->getMessage() === 'Invalid PIN code.') {
                $statusCode = 422;
            }
            
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Process deposit request
     */
    public function deposit(CreateDepositRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            // For now, we'll just return the deposit configuration
            // In a real implementation, this would process the deposit
            $result = [
                'message' => 'Deposit request received. Please send funds to the deposit address.',
                'amount' => $request->input('amount'),
            ];

            return $this->successResponse($result, 'Deposit request has been created successfully.');
        } catch (Exception $e) {
            AuditLogger::logSecurityEvent('deposit_failed', 'error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Handle deposit callback from payment gateway
     */
    public function handleDepositCallback(Request $request): JsonResponse
    {
        try {
            // In a real implementation, this would process the callback from the payment gateway
            $result = [
                'message' => 'Deposit callback received and processed.',
            ];

            return $this->successResponse($result, 'Deposit callback processed successfully.');
        } catch (Exception $e) {
            AuditLogger::logSecurityEvent('deposit_callback_failed', 'error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get deposit configuration
     */
    public function depositConfig(): JsonResponse
    {
        $config = [
            'min_deposit' => config('wallet.min_deposit', 1.0),
            'max_deposit' => config('wallet.max_deposit', 10000.0),
            'supported_currencies' => config('wallet.supported_currencies', ['USD']),
        ];

        return $this->successResponse($config, 'Deposit configuration fetched successfully');
    }

    /**
     * Get wallet configuration
     */
    public function config(): JsonResponse
    {
        $config = [
            'min_deposit' => config('wallet.min_deposit', 1.0),
            'max_deposit' => config('wallet.max_deposit', 10000.0),
            'min_withdrawal' => config('wallet.min_withdrawal', 1.0),
            'max_withdrawal' => config('wallet.max_withdrawal', 10000.0),
            'network_fee' => config('wallet.network_fee', 0.1),
            'processing_time' => config('wallet.processing_time', '24-48 hours'),
            'supported_currencies' => config('wallet.supported_currencies', ['USD']),
        ];

        return $this->successResponse($config, 'Wallet configuration fetched successfully');
    }
}