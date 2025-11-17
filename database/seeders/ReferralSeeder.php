<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ReferralSeeder extends Seeder
{
    public function run(): void
    {
        // Находим нашего основного пользователя (предполагаем, что он первый)
        $mainUser = User::first();

        if (!$mainUser) {
            return; // Если пользователей нет, выходим
        }

        // Создаем 3 рефералов, приглашенных нашим основным пользователем
        $referral1 = User::create([
            'username' => 'Александр',
            'referral_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
            'referrer_id' => $mainUser->id,
        ]);

        $referral2 = User::create([
            'username' => 'Мария',
            'referral_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
            'referrer_id' => $mainUser->id,
        ]);

        $referral3 = User::create([
            'username' => 'Дмитрий',
            'referral_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
            'referrer_id' => $mainUser->id,
        ]);

        // Имитируем начисления от этих рефералов нашему основному пользователю
        $mainUser->referralEarnings()->create([
            'referral_id' => $referral1->id,
            'amount' => 45.20,
            'description' => 'Бонус от игры реферала',
        ]);

        $mainUser->referralEarnings()->create([
            'referral_id' => $referral2->id,
            'amount' => 32.50,
            'description' => 'Бонус от игры реферала',
        ]);

        $mainUser->referralEarnings()->create([
            'referral_id' => $referral3->id,
            'amount' => 28.90,
            'description' => 'Бонус от игры реферала',
        ]);

        // Добавим еще одно начисление от первого реферала, чтобы проверить суммирование
        $mainUser->referralEarnings()->create([
            'referral_id' => $referral1->id,
            'amount' => 10.00,
            'description' => 'Бонус от турнира',
        ]);
    }
}
