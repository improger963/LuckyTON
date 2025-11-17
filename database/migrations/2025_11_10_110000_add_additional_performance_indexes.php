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
        // Add composite indexes for common query patterns
        
        // Add index for wallets table - user_id is frequently used for lookups
        Schema::table('wallets', function (Blueprint $table) {
            $table->index('user_id');
        });

        // Add indexes for referral_earnings table
        Schema::table('referral_earnings', function (Blueprint $table) {
            // Composite index for common queries filtering by user_id and created_at
            $table->index(['user_id', 'created_at']);
            
            // Index for referral_id to speed up queries on referred users
            $table->index('referral_id');
        });

        // Add indexes for game_states table
        Schema::table('game_states', function (Blueprint $table) {
            // room_id is already unique, but add index for faster lookups
            $table->index('room_id');
            
            // created_at is used for sorting and time-based queries
            $table->index('created_at');
        });

        // Add composite indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Composite index for common queries filtering by is_premium and created_at
            $table->index(['is_premium', 'created_at']);
            
            // Composite index for common queries filtering by banned_at and created_at
            $table->index(['banned_at', 'created_at']);
            
            // Index for referral_code to speed up referral lookups
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from wallets table
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        // Remove indexes from referral_earnings table
        Schema::table('referral_earnings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['referral_id']);
        });

        // Remove indexes from game_states table
        Schema::table('game_states', function (Blueprint $table) {
            $table->dropIndex(['room_id']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_premium', 'created_at']);
            $table->dropIndex(['banned_at', 'created_at']);
            $table->dropIndex(['referral_code']);
        });
    }
};