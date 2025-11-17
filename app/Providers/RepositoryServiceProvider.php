<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WalletRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\GameRoomRepository;
use App\Repositories\Eloquent\GameRoomPlayerRepository;
use App\Repositories\Eloquent\TournamentRepository;
use App\Repositories\Eloquent\ReferralEarningRepository;
use App\Repositories\Eloquent\GameStateRepository;
use App\Repositories\Eloquent\AdminRepository;
use App\Repositories\Eloquent\SocialAccountRepository;
use App\Services\WalletService;
use App\Services\GameService;
use App\Services\GameRoomService;
use App\Services\TournamentService;
use App\Services\UserService;
use App\Services\TransactionService;
use App\Services\Cache\GameCacheService;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\GameRoom;
use App\Models\Tournament;
use App\Models\ReferralEarning;
use App\Models\GameState;
use App\Models\Admin;
use App\Models\SocialAccount;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new User());
        });

        $this->app->bind(WalletRepository::class, function ($app) {
            return new WalletRepository(new Wallet());
        });

        $this->app->bind(TransactionRepository::class, function ($app) {
            return new TransactionRepository(new Transaction());
        });

        $this->app->bind(GameRoomRepository::class, function ($app) {
            return new GameRoomRepository(new GameRoom());
        });

        $this->app->bind(GameRoomPlayerRepository::class, function ($app) {
            return new GameRoomPlayerRepository();
        });

        $this->app->bind(TournamentRepository::class, function ($app) {
            return new TournamentRepository(new Tournament());
        });

        $this->app->bind(ReferralEarningRepository::class, function ($app) {
            return new ReferralEarningRepository(new ReferralEarning());
        });

        $this->app->bind(GameStateRepository::class, function ($app) {
            return new GameStateRepository(new GameState());
        });

        $this->app->bind(AdminRepository::class, function ($app) {
            return new AdminRepository(new Admin());
        });

        $this->app->bind(SocialAccountRepository::class, function ($app) {
            return new SocialAccountRepository(new SocialAccount());
        });

        // Services
        $this->app->bind(WalletService::class, function ($app) {
            return new WalletService(
                $app->make(WalletRepository::class),
                $app->make(TransactionRepository::class),
                $app->make(UserRepository::class)
            );
        });

        $this->app->bind(GameService::class, function ($app) {
            return new GameService(
                $app->make(GameRoomRepository::class),
                $app->make(UserRepository::class),
                $app->make(GameRoomPlayerRepository::class),
                $app->make(WalletService::class),
                $app->make(GameCacheService::class)
            );
        });

        $this->app->bind(TournamentService::class, function ($app) {
            return new TournamentService(
                $app->make(TournamentRepository::class),
                $app->make(UserRepository::class)
            );
        });

        $this->app->bind(GameRoomService::class, function ($app) {
            return new GameRoomService(
                $app->make(GameRoomRepository::class),
                $app->make(GameRoomPlayerRepository::class),
                $app->make(WalletService::class),
                $app->make(GameService::class)
            );
        });

        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepository::class)
            );
        });

        $this->app->bind(TransactionService::class, function ($app) {
            return new TransactionService(
                $app->make(TransactionRepository::class)
            );
        });

        // Cache Services
        $this->app->bind(GameCacheService::class, function ($app) {
            return new GameCacheService(
                $app->make(GameRoomRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}