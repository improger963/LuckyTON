<?php

return [
    'min_withdrawal' => env('MIN_WITHDRAWAL_AMOUNT', 1.0),
    'max_withdrawal' => env('MAX_WITHDRAWAL_AMOUNT', 10000.0),
    'network_fee' => env('NETWORK_FEE', 0.1),
    'currency' => env('WALLET_CURRENCY', 'USD'),
    'network' => env('CRYPTO_NETWORK', 'TRC20'),
    'auto_generate_address' => env('AUTO_GENERATE_DEPOSIT_ADDRESS', true),
    'min_deposit' => env('MIN_DEPOSIT_AMOUNT', 1.0),
    'max_deposit' => env('MAX_DEPOSIT_AMOUNT', 10000.0),
    'webhook_secret' => env('WEBHOOK_SECRET'),
    'supported_networks' => ['TRC20', 'ERC20'],
    'payment_systems' => [
        [
            "id" => "crypto_trc20_usdt",
            "name" => "Crypto (USDT TRC-20)"
        ],
              [
            "id" => "another_payment_system",
            "name" => "Другая Платежная Система"
        ]
    ],
];
