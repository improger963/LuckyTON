<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TelegramHandlerController extends Controller
{
    /**
     * Handle Telegram authentication callback
     */
    public function handleTelegramCallback(Request $request)
    {
        try {
            Log::info('Telegram authentication started');

            $initData = $request->input('initData');

            // Log the raw initData for debugging
            Log::info('Raw initData', ['init_data' => $initData]);

            if (!$initData) {
                return response()->json([
                    'success' => false,
                    'message' => 'initData is required'
                ], 400);
            }

            // Parse initData first to check what verification method to use
            parse_str($initData, $parsedData);

            // Log the parsed data for debugging
            Log::info('Parsed initData', ['parsed_data' => $parsedData]);

            // Check if verification is enabled
            $shouldVerify = config('services.telegram.verify_data', true);

            if ($shouldVerify) {
                // Verify Telegram data
                $verification = $this->verifyTelegramData($initData, $parsedData);

                Log::info('Telegram data verification result', [
                    'is_valid' => $verification['valid'] ?? false,
                    'method_used' => $verification['method'] ?? 'unknown'
                ]);

                if (!$verification['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid Telegram data'
                    ], 401);
                }
            } else {
                Log::info('Telegram data verification skipped (disabled in config)');
            }

            // Extract user data
            $telegramUser = $this->extractTelegramUserData($parsedData);

            Log::info('Extracted Telegram user data', ['telegram_user' => $telegramUser]);

            if (empty($telegramUser['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing Telegram user ID'
                ], 400);
            }

            // Find or create user
            $user = $this->findOrCreateUser($telegramUser);

            // Login
            Auth::login($user);

            // Create the response
            $responseData = [
                'success' => true,
                'message' => 'Authentication successful',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'referral_code' => $user->referral_code,
                ],
            ];

            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            Log::error('Telegram authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Telegram WebApp initData
     */
    private function verifyTelegramData(string $initData, array $parsedData): array
    {
        Log::info('Starting Telegram data verification', [
            'has_signature' => isset($parsedData['signature']),
            'has_hash' => isset($parsedData['hash'])
        ]);

        // Check required fields
        if (empty($parsedData['auth_date'])) {
            return ['valid' => false];
        }

        // Check expiration (24 hours)
        $authDate = (int)$parsedData['auth_date'];
        if (time() - $authDate > 86400) {
            return ['valid' => false];
        }

        // Try Ed25519 first if signature exists
        if (isset($parsedData['signature'])) {
            Log::info('Using Ed25519 signature verification');
            $result = $this->verifyWithSignature($parsedData);
            if ($result['valid']) {
                return $result;
            }
        }

        // Fallback to bot token verification
        if (isset($parsedData['hash'])) {
            Log::info('Using bot token hash verification');
            return $this->verifyWithBotToken($parsedData);
        }

        return ['valid' => false];
    }

    /**
     * Verify using Ed25519 signature
     */
    private function verifyWithSignature(array $parsedData): array
    {
        if (!isset($parsedData['signature'])) {
            return ['valid' => false];
        }

        $botToken = config('services.telegram.bot_token');
        if (!$botToken) {
            return ['valid' => false];
        }

        // Extract bot ID from token
        $botId = explode(':', $botToken)[0] ?? null;
        if (!$botId) {
            return ['valid' => false];
        }

        $signatureBase64 = $parsedData['signature'];

        // Proper base64 decoding with URL-safe characters
        $signatureBase64 = str_replace(['-', '_'], ['+', '/'], $signatureBase64);

        // Add padding
        $padding = strlen($signatureBase64) % 4;
        if ($padding > 0) {
            $signatureBase64 .= str_repeat('=', 4 - $padding);
        }

        $signature = base64_decode($signatureBase64);

        if ($signature === false || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            Log::error('Invalid signature length', [
                'signature_length' => $signature ? strlen($signature) : 0,
                'expected_length' => SODIUM_CRYPTO_SIGN_BYTES,
                'signature_base64' => $parsedData['signature']
            ]);
            return ['valid' => false];
        }

        // Remove signature and hash from data
        unset($parsedData['signature']);
        if (isset($parsedData['hash'])) {
            unset($parsedData['hash']);
        }

        // Create sorted key=value pairs
        $dataCheckArr = [];
        foreach ($parsedData as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);

        // Create verification string
        $dataCheckString = implode("\n", $dataCheckArr);
        $verificationString = $botId . ":WebAppData\n" . $dataCheckString;

        // Telegram production public key
        $publicKeyHex = 'e7bf03a2fa4602af4580703d88dda5bb59f32ed8b02a56c187fe7d34caed242d';
        $publicKey = hex2bin($publicKeyHex);

        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            Log::error('Invalid public key');
            return ['valid' => false];
        }

        // Verify Ed25519 signature
        try {
            $isValid = sodium_crypto_sign_verify_detached($signature, $verificationString, $publicKey);

            Log::info('Ed25519 verification result', [
                'is_valid' => $isValid,
                'signature_length' => strlen($signature),
                'verification_string_length' => strlen($verificationString)
            ]);

            return [
                'valid' => $isValid,
                'method' => 'ed25519'
            ];
        } catch (\Exception $e) {
            Log::error('Ed25519 verification error', [
                'error' => $e->getMessage()
            ]);

            return ['valid' => false];
        }
    }

    /**
     * Verify using bot token (HMAC-SHA256)
     */
    private function verifyWithBotToken(array $parsedData): array
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) {
            return ['valid' => false];
        }

        // Extract hash
        $receivedHash = $parsedData['hash'];
        unset($parsedData['hash']);

        // Remove signature if it exists
        if (isset($parsedData['signature'])) {
            unset($parsedData['signature']);
        }

        // Create sorted key=value pairs
        $dataCheckArr = [];
        foreach ($parsedData as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);

        // Create data check string
        $dataCheckString = implode("\n", $dataCheckArr);

        // Generate secret key
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

        // Generate hash
        $calculatedHash = bin2hex(
            hash_hmac('sha256', $dataCheckString, $secretKey, true)
        );

        // Compare hashes
        $isValid = hash_equals($calculatedHash, $receivedHash);

        Log::info('Bot token verification result', [
            'is_valid' => $isValid
        ]);

        return [
            'valid' => $isValid,
            'method' => 'bot_token'
        ];
    }

    /**
     * Extract Telegram user data
     */
    private function extractTelegramUserData(array $data): array
    {
        if (!isset($data['user'])) {
            Log::warning('Missing user data in Telegram initData', ['data' => $data]);
            return ['id' => null];
        }

        // Log the user data for debugging
        Log::info('Raw user data', ['user' => $data['user']]);

        // Try to decode user data
        $userJson = json_decode($data['user'], true);

        // Log JSON decode result
        Log::info('JSON decode result', [
            'user_json' => $userJson,
            'json_error' => json_last_error(),
            'json_error_msg' => json_last_error_msg()
        ]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try URL decode first, then JSON decode
            $urlDecoded = urldecode($data['user']);
            Log::info('URL decoded user data', ['url_decoded' => $urlDecoded]);

            $userJson = json_decode($urlDecoded, true);

            Log::info('JSON decode after URL decode', [
                'user_json' => $userJson,
                'json_error' => json_last_error(),
                'json_error_msg' => json_last_error_msg()
            ]);
        }

        if (!$userJson || json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to decode Telegram user data', [
                'raw_user_data' => $data['user'],
                'json_error' => json_last_error(),
                'json_error_msg' => json_last_error_msg()
            ]);
            return ['id' => null];
        }

        return [
            'id' => $userJson['id'] ?? null,
            'first_name' => $userJson['first_name'] ?? '',
            'last_name' => $userJson['last_name'] ?? null,
            'username' => $userJson['username'] ?? null,
            'photo_url' => $userJson['photo_url'] ?? null,
            'language_code' => $userJson['language_code'] ?? null,
            'is_premium' => $userJson['is_premium'] ?? false,
        ];
    }

    /**
     * Find or create user
     */
    private function findOrCreateUser(array $telegramData): User
    {
        return DB::transaction(function () use ($telegramData) {
            // Find existing social account
            $socialAccount = SocialAccount::with('user')
                ->where('provider_name', 'telegram')
                ->where('provider_id', $telegramData['id'])
                ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;

                if ($user->banned_at) {
                    throw new \Exception('Your account has been suspended.');
                }

                return $user;
            }

            // Create new user
            $username = $telegramData['username'] ?? 'telegram_' . $telegramData['id'];
            $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);

            if (empty($username)) {
                $username = 'telegram_' . $telegramData['id'];
            }

            // Ensure username is unique
            $originalUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername . '_' . $counter;
                $counter++;
            }

            $user = User::create([
                'username' => $username,
                'email' => null,
                'password' => Hash::make(Str::random(32)),
                'referral_code' => $this->generateUniqueReferralCode(),
            ]);

            // Create wallet
            $user->wallet()->create();

            // Create social account
            $user->socialAccounts()->create([
                'provider_name' => 'telegram',
                'provider_id' => $telegramData['id'],
            ]);

            return $user;
        });
    }

    /**
     * Generate unique referral code
     */
    private function generateUniqueReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
