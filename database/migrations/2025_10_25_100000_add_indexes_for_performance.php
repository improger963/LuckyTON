<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to wallets table
        Schema::table('wallets', function (Blueprint $table) {
            // Balance is frequently queried for sufficiency checks
            $table->index('balance');
        });

        // Add indexes to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            // Type and status are frequently used for filtering
            $table->index('type');
            $table->index('status');
            
            // Composite index for common queries filtering by type and status
            $table->index(['type', 'status']);
            
            // Wallet_id is frequently used for joining
            $table->index('wallet_id');
            
            // Created_at is used for sorting and time-based queries
            $table->index('created_at');
        });

        // Add indexes to game_rooms table
        Schema::table('game_rooms', function (Blueprint $table) {
            // Game_type and status are frequently used for filtering
            $table->index('game_type');
            $table->index('status');
            
            // Composite index for common queries filtering by game_type and status
            $table->index(['game_type', 'status']);
            
            // Stake is used for sorting and filtering
            $table->index('stake');
            
            // Created_at is used for sorting
            $table->index('created_at');
        });

        // Add indexes to tournaments table
        Schema::table('tournaments', function (Blueprint $table) {
            // Game_type and status are frequently used for filtering
            $table->index('game_type');
            $table->index('status');
            
            // Composite index for common queries filtering by game_type and status
            $table->index(['game_type', 'status']);
            
            // Buy_in is used for sorting and filtering
            $table->index('buy_in');
            
            // Starts_at is frequently used for time-based queries
            $table->index('starts_at');
            
            // Registration_opens_at is frequently used for time-based queries
            $table->index('registration_opens_at');
            
            // Created_at is used for sorting
            $table->index('created_at');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            // Is_premium is used for filtering
            $table->index('is_premium');
            
            // Banned_at is used for filtering
            $table->index('banned_at');
            
            // Referrer_id is used for referral queries
            $table->index('referrer_id');
            
            // Created_at is used for sorting
            $table->index('created_at');
        });

        // Add indexes to game_room_players table
        Schema::table('game_room_players', function (Blueprint $table) {
            // User_id and game_room_id are frequently used for joining
            $table->index('user_id');
            $table->index('game_room_id');
            
            // Is_ready is used for filtering
            $table->index('is_ready');
            
            // Created_at is used for sorting
            $table->index('created_at');
        });

        // Add indexes to tournament_players table
        Schema::table('tournament_players', function (Blueprint $table) {
            // Tournament_id and user_id are frequently used for joining
            $table->index('tournament_id');
            $table->index('user_id');
            
            // Place is used for sorting
            $table->index('place');
            
            // Created_at is used for sorting
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from wallets table
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['balance']);
        });

        // Remove indexes from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['wallet_id']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from game_rooms table
        Schema::table('game_rooms', function (Blueprint $table) {
            $table->dropIndex(['game_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['game_type', 'status']);
            $table->dropIndex(['stake']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from tournaments table
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropIndex(['game_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['game_type', 'status']);
            $table->dropIndex(['buy_in']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['registration_opens_at']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_premium']);
            $table->dropIndex(['banned_at']);
            $table->dropIndex(['referrer_id']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from game_room_players table
        Schema::table('game_room_players', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['game_room_id']);
            $table->dropIndex(['is_ready']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from tournament_players table
        Schema::table('tournament_players', function (Blueprint $table) {
            $table->dropIndex(['tournament_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['place']);
            $table->dropIndex(['created_at']);
        });
    }
};