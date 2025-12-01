<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display users listing
     */
    public function index(): View
    {
        $users = User::with('wallet')
            ->withCount('referrals')
            ->latest()
            ->paginate(15);

        return view('admin.users.modern-index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show(User $user): View
    {
        $user->load([
            'wallet.transactions' => function ($query) {
                $query->latest()->limit(10);
            },
            'referrals',
            'gameRooms',
            'tournaments'
        ]);

        $userStats = $this->getUserStats($user);

        return view('admin.users.show', compact('user', 'userStats'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_premium' => ['sometimes', 'boolean'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update($validator->validated());

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User information updated successfully.');
    }

    /**
     * Ban user
     */
    public function ban(User $user): RedirectResponse
    {
        try {
            if ($user->banned_at) {
                return redirect()->back()
                    ->with('error', 'User is already banned.');
            }

            $user->update([
                'banned_at' => now(),
                'ban_reason' => 'Banned by admin',
            ]);

            return redirect()->back()
                ->with('success', 'User has been banned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Unban user
     */
    public function unban(User $user): RedirectResponse
    {
        try {
            if (!$user->banned_at) {
                return redirect()->back()
                    ->with('error', 'User is not banned.');
            }

            $user->update([
                'banned_at' => null,
                'ban_reason' => null,
            ]);

            return redirect()->back()
                ->with('success', 'User has been unbanned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Adjust user balance
     */
    public function adjustBalance(Request $request, User $user): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:deposit,withdrawal'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();

        try {
            $amount = $validatedData['amount'];
            $reason = $validatedData['reason'];
            $type = $validatedData['type'];

            $transaction = DB::transaction(function () use ($user, $amount, $reason, $type) {
                // Get wallet with pessimistic lock
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

                if (!$wallet) {
                    return redirect()->back()
                        ->withErrors(['amount' => 'User wallet not found.']);
                }

                // Validate withdrawal amount
                if ($type === Transaction::TYPE_WITHDRAWAL && $wallet->balance < abs($amount)) {
                    return redirect()->back()
                        ->withErrors(['amount' => 'Cannot subtract more than the current balance.']);
                }

                // Update wallet balance
                if ($type === Transaction::TYPE_DEPOSIT) {
                    $wallet->increment('balance', $amount);
                } else {
                    $wallet->decrement('balance', abs($amount));
                }

                // Create transaction record
                return $wallet->transactions()->create([
                    'type' => $type,
                    'amount' => $type === Transaction::TYPE_DEPOSIT ? $amount : -abs($amount),
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => $reason,
                    'metadata' => [
                        'admin_action' => true,
                        'admin_id' => auth('admin')->id(),
                        'admin_name' => auth('admin')->user()->name,
                    ],
                ]);
            });

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User balance adjusted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStats(User $user): array
    {
        $wallet = $user->wallet;
        
        return [
            'total_balance' => $wallet ? $wallet->balance : 0,
            'total_transactions' => $wallet ? $wallet->transactions()->count() : 0,
            'total_referrals' => $user->referrals()->count(),
            'total_games_played' => 0, // TODO: Add game history tracking
            'total_tournaments_played' => $user->tournaments()->count(),
        ];
    }
}