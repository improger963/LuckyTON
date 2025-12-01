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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game_type'); // 'poker' или 'blot'

            // Финансы
            $table->decimal('prize_pool', 16, 8);
            $table->decimal('buy_in', 16, 8);

            // Игроки
            $table->unsignedInteger('max_players');

            // Время
            $table->timestamp('registration_opens_at'); // Когда открывается регистрация
            $table->timestamp('starts_at'); // Когда начинается турнир

            // Статус
            $table->enum('status', [
                'draft', // Черновик
                'registration_open', // Идет регистрация
                'registration_closed', // Регистрация закрыта
                'in_progress', // Идет игра
                'completed', // Завершен
                'cancelled' // Отменен
            ])->default('draft');
            
            // Отмена турнира
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
