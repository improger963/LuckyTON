<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\Auth\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\GameRoomController as AdminGameRoomController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\Auth\TelegramHandlerController;
use Illuminate\Http\Request;

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])
            ->name('login');

        Route::post('login', [AdminAuthController::class, 'store'])
            ->name('login.store')
            ->middleware('throttle:auth'); // Use our auth rate limiter
    });

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Logout
        Route::post('logout', [AdminAuthController::class, 'destroy'])
            ->name('logout');

        // User management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('', [AdminUserController::class, 'index'])
                ->name('index');

            Route::get('create', [AdminUserController::class, 'create'])
                ->name('create');

            Route::post('', [AdminUserController::class, 'store'])
                ->name('store');

            Route::get('{user}', [AdminUserController::class, 'show'])
                ->name('show');

            Route::get('{user}/edit', [AdminUserController::class, 'edit'])
                ->name('edit');

            Route::put('{user}', [AdminUserController::class, 'update'])
                ->name('update');

            Route::delete('{user}', [AdminUserController::class, 'destroy'])
                ->name('destroy');

            Route::post('{user}/ban', [AdminUserController::class, 'ban'])
                ->name('ban');

            Route::post('{user}/unban', [AdminUserController::class, 'unban'])
                ->name('unban');

            Route::post('{user}/balance', [AdminUserController::class, 'adjustBalance'])
                ->name('balance.adjust');
        });

        // Withdrawal management
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('', [AdminWithdrawalController::class, 'index'])
                ->name('index');

            Route::get('{transaction}', [AdminWithdrawalController::class, 'show'])
                ->name('show');

            Route::post('{transaction}/approve', [AdminWithdrawalController::class, 'approve'])
                ->name('approve');

            Route::post('{transaction}/reject', [AdminWithdrawalController::class, 'reject'])
                ->name('reject');

            Route::post('{transaction}/cancel', [AdminWithdrawalController::class, 'cancel'])
                ->name('cancel');
        });

        // Game rooms management
        Route::prefix('game-rooms')->name('gamerooms.')->group(function () {
            Route::get('', [AdminGameRoomController::class, 'index'])
                ->name('index');

            Route::get('create', [AdminGameRoomController::class, 'create'])
                ->name('create');

            Route::post('', [AdminGameRoomController::class, 'store'])
                ->name('store');

            Route::get('{room}', [AdminGameRoomController::class, 'show'])
                ->name('show');

            Route::get('{room}/edit', [AdminGameRoomController::class, 'edit'])
                ->name('edit');

            Route::put('{room}', [AdminGameRoomController::class, 'update'])
                ->name('update');

            Route::delete('{room}', [AdminGameRoomController::class, 'destroy'])
                ->name('destroy');

            Route::post('{room}/start', [AdminGameRoomController::class, 'start'])
                ->name('start');

            Route::post('{room}/complete', [AdminGameRoomController::class, 'complete'])
                ->name('complete');

            Route::post('{room}/cancel', [AdminGameRoomController::class, 'cancel'])
                ->name('cancel');

            Route::post('{room}/enable', [AdminGameRoomController::class, 'enable'])
                ->name('enable');
        });

        // Tournaments management
        Route::prefix('tournaments')->name('tournaments.')->group(function () {
            Route::get('', [AdminTournamentController::class, 'index'])
                ->name('index');

            Route::get('create', [AdminTournamentController::class, 'create'])
                ->name('create');

            Route::post('', [AdminTournamentController::class, 'store'])
                ->name('store');

            Route::get('{tournament}', [AdminTournamentController::class, 'show'])
                ->name('show');

            Route::get('{tournament}/edit', [AdminTournamentController::class, 'edit'])
                ->name('edit');

            Route::put('{tournament}', [AdminTournamentController::class, 'update'])
                ->name('update');

            Route::delete('{tournament}', [AdminTournamentController::class, 'destroy'])
                ->name('destroy');
        });
    });
});

// Public API routes
Route::prefix('api')->group(function () {
    // Health check endpoint
    Route::get('/ping', function () {
        return response()->json(['message' => 'pong']);
    });

    Route::get('/test-cors', function () {
        return response()->json(['message' => 'CORS is working!']);
    });

    Route::get('/test-broadcast', function () {
        // Test event broadcasting
        broadcast(new \App\Events\PlayerJoinedRoom(
            \App\Models\User::first(),
            \App\Models\GameRoom::first()
        ));
        
        return response()->json(['message' => 'Test event broadcasted']);
    });

    // Public authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('telegram/callback', [TelegramHandlerController::class, 'handleTelegramCallback'])
            ->name('auth.telegram.callback');

        Route::post('register', [AuthController::class, 'register'])
            ->name('auth.register')
            ->middleware('throttle:auth'); // Use our auth rate limiter

        Route::post('login', [AuthController::class, 'login'])
            ->name('auth.login')
            ->middleware('throttle:auth'); // Use our auth rate limiter

        Route::post('forgot-password', [AuthController::class, 'sendResetLink'])
            ->name('auth.password.forgot')
            ->middleware('throttle:auth'); // Use our auth rate limiter

        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->name('auth.password.reset')
            ->middleware('throttle:auth'); // Use our auth rate limiter
    });

    // Public routes that don't require authentication
    Route::post('payment/webhook', function (Request $request) {
        // Verify webhook signature
        $secret = config('services.webhook.secret') ?? config('platform.webhook_secret');

        // If no secret configured, fail verification
        if (!$secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get signature from header
        $signature = $request->header('X-Webhook-Signature');

        // If no signature provided, fail verification
        if (!$signature) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        \Illuminate\Support\Facades\Log::info("Payment Webhook", $request->all());

        return response()->json(['status' => 'ok']);
    });
});

// Protected API routes - using web middleware for SPA authentication
Route::prefix('api')->middleware('auth:sanctum')->group(function () {

    // User profile
    Route::get('/user', [ProfileController::class, 'show'])->name('user.profile');

    // Profile management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::post('pin', [ProfileController::class, 'setPin'])
            ->name('pin.set');

        Route::put('pin/toggle', [ProfileController::class, 'togglePin'])
            ->name('pin.toggle');

        Route::post('password', [ProfileController::class, 'changePassword'])
            ->name('password.change');

        Route::get('security-status', [ProfileController::class, 'getSecurityStatus'])
            ->name('security.status');
    });

    // Wallet management
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('balance', [WalletController::class, 'show'])
            ->name('balance')
            ->middleware('verify.wallet');

        Route::get('history', [WalletController::class, 'history'])
            ->name('history')
            ->middleware('verify.wallet');

        Route::post('deposit', [WalletController::class, 'deposit'])
            ->name('deposit')
            ->middleware(['throttle:financial', 'verify.wallet']);

        Route::get('deposit/config', [WalletController::class, 'depositConfig'])
            ->name('deposit.config')
            ->middleware('verify.wallet');

        Route::post('withdraw', [WalletController::class, 'withdraw'])
            ->name('withdraw')
            ->middleware(['throttle:financial', 'verify.wallet']);

        Route::get('config', [WalletController::class, 'config'])
            ->name('config')
            ->middleware('verify.wallet');
    });

    // Deposit callback endpoint (for payment gateway)
    Route::post('wallet/deposit/callback', [WalletController::class, 'handleDepositCallback'])
        ->name('wallet.deposit.callback')
        ->withoutMiddleware('auth:sanctum'); // Payment gateway won't have auth token

    // Game rooms
    Route::prefix('game-rooms')->name('game-rooms.')->group(function () {
        Route::get('', [GameController::class, 'index'])
            ->name('index');

        // These routes are available to all authenticated users
        Route::post('{room}/join', [GameController::class, 'join'])
            ->name('join')
            ->middleware('auth:sanctum');

        Route::get('{room}', [GameController::class, 'show'])
            ->name('show')
            ->middleware('auth:sanctum');

        // These routes are only available to room participants
        Route::prefix('{room}')
            ->middleware(['auth:sanctum', 'verify.game.participation'])
            ->group(function () {
                Route::post('/move', [GameController::class, 'move'])
                    ->name('move')
                    ->middleware('throttle:game-action');

                Route::post('/sit', [GameController::class, 'sit'])
                    ->name('sit')
                    ->middleware('throttle:game-action');

                Route::post('/stand-up', [GameController::class, 'standUp'])
                    ->name('standUp')
                    ->middleware('throttle:game-action');

                Route::post('/leave', [GameController::class, 'leave'])
                    ->name('leave')
                    ->middleware('throttle:game-action');

                // Blot-specific routes
                Route::post('/blot/select-trump', [\App\Http\Controllers\BlotController::class, 'selectTrump'])
                    ->name('blot.selectTrump')
                    ->middleware('throttle:game-action');

                Route::post('/blot/announce-combination', [\App\Http\Controllers\BlotController::class, 'announceCombination'])
                    ->name('blot.announceCombination')
                    ->middleware('throttle:game-action');
            });
    });

    // Referral system
    Route::prefix('referral')->name('referral.')->group(function () {
        Route::get('', [ReferralController::class, 'show'])
            ->name('stats');

        Route::get('activity', [ReferralController::class, 'activity'])
            ->name('activity');

        Route::get('links', [ReferralController::class, 'links'])
            ->name('links');

        Route::get('share', [ReferralController::class, 'share'])
            ->name('share');
    });

    // Tournaments
    Route::prefix('tournaments')->name('tournaments.')->group(function () {
        Route::get('', [TournamentController::class, 'index'])
            ->name('index');

        Route::post('{tournament}/register', [TournamentController::class, 'register'])
            ->name('register')
            ->middleware('throttle:financial'); // Financial operation

        Route::get('{tournament}', [TournamentController::class, 'show'])
            ->name('show');
    });

    // Authentication
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
});

// Fallback route for SPA
Route::fallback(function () {
    return 'Laravel';
});