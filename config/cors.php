<?php

return [

    "paths" => ["api/*", "sanctum/csrf-cookie", "broadcasting/auth", "auth/telegram/callback"],

    "allowed_methods" => ["*"],

    "allowed_origins" => [
        "https://luckyton.app",
        "https://www.luckyton.app", 
        "https://blot-client.vercel.app",
        "https://undeclaimed-nonscheduled-jarrod.ngrok-free.dev",
        "http://localhost:5173",
        "http://localhost",
    ],

    "allowed_origins_patterns" => [
        "#^https://.+\\.vercel\\.app$#",
        "#^https://.+\\.ngrok-free\\.dev$#",
    ],

    "allowed_headers" => ["*"],

    "exposed_headers" => [],

    "max_age" => 0,

    "supports_credentials" => true,

];
