<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Находим первого пользователя в базе данных
        $user = User::first();

        // Если пользователь существует и у него есть кошелек
        if ($user && $user->wallet) {
            $wallet = $user->wallet;

            // Удаляем старые транзакции этого кошелька, чтобы не было дублей при повторном запуске
            Transaction::where('wallet_id', $wallet->id)->delete();

            // Создаем несколько тестовых транзакций
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'game_win',
                'amount' => 250.00,
                'status' => 'completed',
                'description' => 'Покер - Профи',
                'created_at' => now()->subDays(3)->setTime(14, 30), // 3 дня назад в 14:30
            ]);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'game_loss',
                'amount' => -20.00, // Обратите внимание на отрицательное значение
                'status' => 'completed',
                'description' => 'Блот - Классический',
                'created_at' => now()->subDays(3)->setTime(12, 15),
            ]);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'game_win',
                'amount' => 1200.00,
                'status' => 'completed',
                'description' => 'Турнир - Еженедельный',
                'created_at' => now()->subDays(4)->setTime(20, 45),
            ]);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => 100.00,
                'status' => 'completed',
                'description' => 'Пополнение счета',
                'created_at' => now()->subDays(5),
            ]);
        }
    }
}
