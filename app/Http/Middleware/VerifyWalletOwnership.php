<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Wallet;

class VerifyWalletOwnership
{
    /**
     * Handle an incoming request to verify wallet ownership.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $walletId = $request->route('wallet') ?? $request->input('wallet_id');
        
        if ($walletId) {
            $wallet = Wallet::find($walletId);
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            if (!$wallet || $wallet->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        return $next($request);
    }
}