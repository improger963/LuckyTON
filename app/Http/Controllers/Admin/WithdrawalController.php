<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    /**
     * Display pending withdrawals
     */
    public function index(): View
    {
        $withdrawals = Transaction::where('type', Transaction::TYPE_WITHDRAWAL)
            ->where('status', Transaction::STATUS_PENDING)
            ->with(['wallet.user'])
            ->latest()
            ->paginate(15);

        return view('admin.withdrawals.modern-index', ['withdrawals' => $withdrawals]);
    }

    /**
     * Show withdrawal details
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['wallet.user']);

        return view('admin.withdrawals.show', ['transaction' => $transaction]);
    }

    /**
     * Approve withdrawal
     */
    public function approve(Transaction $transaction): RedirectResponse
    {
        try {
            if ($transaction->type !== Transaction::TYPE_WITHDRAWAL) {
                return redirect()->back()
                    ->with('error', 'Only withdrawal transactions can be approved.');
            }

            if ($transaction->status !== Transaction::STATUS_PENDING) {
                return redirect()->back()
                    ->with('error', 'Only pending withdrawals can be approved.');
            }

            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'approved_by' => auth('admin')->id(),
                    'approved_at' => now(),
                    'admin_notes' => 'Approved by admin',
                ]),
            ]);

            return redirect()->back()
                ->with('success', 'Withdrawal approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject withdrawal
     */
    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:255'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            if ($transaction->type !== Transaction::TYPE_WITHDRAWAL) {
                return redirect()->back()
                    ->with('error', 'Only withdrawal transactions can be rejected.');
            }

            if ($transaction->status !== Transaction::STATUS_PENDING) {
                return redirect()->back()
                    ->with('error', 'Only pending withdrawals can be rejected.');
            }

            // Return funds to user's wallet
            $wallet = $transaction->wallet;
            $wallet->increment('balance', abs($transaction->amount));

            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'rejected_by' => auth('admin')->id(),
                    'rejected_at' => now(),
                    'rejection_reason' => $validator->validated()['reason'],
                    'admin_notes' => 'Rejected by admin',
                ]),
            ]);

            return redirect()->back()
                ->with('success', 'Withdrawal rejected and funds returned to user.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Cancel withdrawal
     */
    public function cancel(Request $request, Transaction $transaction): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:255'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            if ($transaction->type !== Transaction::TYPE_WITHDRAWAL) {
                return redirect()->back()
                    ->with('error', 'Only withdrawal transactions can be cancelled.');
            }

            if ($transaction->status !== Transaction::STATUS_PENDING) {
                return redirect()->back()
                    ->with('error', 'Only pending withdrawals can be cancelled.');
            }

            // Return funds to user's wallet
            $wallet = $transaction->wallet;
            $wallet->increment('balance', abs($transaction->amount));

            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'cancelled_by' => auth('admin')->id(),
                    'cancelled_at' => now(),
                    'cancellation_reason' => $validator->validated()['reason'],
                    'admin_notes' => 'Cancelled by admin',
                ]),
            ]);

            return redirect()->back()
                ->with('success', 'Withdrawal cancelled and funds returned to user.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}