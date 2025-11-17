<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Middleware\VerifyWalletOwnership;
use App\Http\Middleware\VerifyGameRoomParticipation;
use App\Providers\RepositoryServiceProvider;
use App\Exceptions\WalletException;
use App\Exceptions\GameException;
use App\Exceptions\TournamentException;
use App\Exceptions\AuthorizationException;
use App\Traits\ApiResponser;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web']] // Changed from ['web', 'auth:sanctum'] to just ['web']
    )
    ->withProviders([
        RepositoryServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Apply CORS to all requests
        $middleware->prepend(HandleCors::class);

        // Removed statefulApi() as we're moving to pure web routes
        // $middleware->statefulApi();
        // $middleware->append(\App\Http\Middleware\LogRequestsMiddleware::class);
        $middleware->append(App\Http\Middleware\ForceHttpCookiesOnLocal::class);
        $middleware->append(App\Http\Middleware\ApiErrorResponseMiddleware::class);

        // Register rate limiting middleware groups
        $middleware->throttleApi();
        
        $middleware->alias([
            'verify.wallet' => VerifyWalletOwnership::class,
            'verify.game.participation' => VerifyGameRoomParticipation::class,
            'throttle:auth' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':auth',
            'throttle:financial' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':financial',
            'throttle:game-action' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':game-action',
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie',
            'broadcasting/auth'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, Illuminate\Http\Request $request) {
            // Проверяем, что запрос пришел на один из наших API-маршрутов
            if ($request->is('api/*') || $request->is('broadcasting/auth')) {
                // Если да, возвращаем JSON-ответ со статусом 401
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        // Handle our custom exceptions
        $exceptions->render(function (WalletException $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => $e->getData()
                ], 400);
            }
        });

        $exceptions->render(function (GameException $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => $e->getData()
                ], 400);
            }
        });

        $exceptions->render(function (TournamentException $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => $e->getData()
                ], 400);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => $e->getData()
                ], 403);
            }
        });
    })
    ->create();