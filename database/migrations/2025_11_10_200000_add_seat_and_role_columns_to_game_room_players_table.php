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
        Schema::table('game_room_players', function (Blueprint $table) {
            $table->string('role')->default('spectator');
            $table->integer('seat')->nullable();
            $table->decimal('stack', 16, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_room_players', function (Blueprint $table) {
            $table->dropColumn(['role', 'seat', 'stack']);
        });
    }
};