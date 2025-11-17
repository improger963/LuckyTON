<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GameRoom;

class GameRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем старые комнаты, чтобы не было дублей
        // GameRoom::truncate();

        // Создаем комнаты для Блота
        GameRoom::create([
            'game_type' => 'blot',
            'name' => 'Блот 2 на 2',
            'stake' => 30,
            'max_players' => 2,
        ]);
        GameRoom::create([
            'game_type' => 'blot',
            'name' => 'Премиум Блот (2 на 2)',
            'stake' => 200,
            'max_players' => 2,
        ]);

        // Создаем комнаты для Покера
        GameRoom::create([
            'game_type' => 'poker',
            'name' => 'Техасский Холдем',
            'stake' => 10,
            'max_players' => 6,
        ]);
        GameRoom::create([
            'game_type' => 'poker',
            'name' => 'Омаха Хай',
            'stake' => 50,
            'max_players' => 6,
        ]);
        GameRoom::create([
            'game_type' => 'poker',
            'name' => 'Большой Стек',
            'stake' => 100,
            'max_players' => 8,
        ]);
    }
}
