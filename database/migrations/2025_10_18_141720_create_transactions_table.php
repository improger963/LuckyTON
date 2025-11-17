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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained()
                ->onDelete('cascade');

            $table->enum('type', ['deposit', 'withdrawal', 'game_win', 'game_loss', 'referral_bonus']);
            $table->decimal('amount', 16, 8);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                ->default('pending');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
