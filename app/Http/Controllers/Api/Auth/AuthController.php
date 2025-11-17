<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\SendResetLinkRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Authenticate user with username and password
            $user = $this->authenticateUser(
                $request->input('username'),
                $request->input('password')
            );

            // Log the user in to establish session
            Auth::login($user);

            // Create response with user data only (no token in JSON)
            return $this->successResponse([
                'user' => $user
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->errorResponse('Invalid credentials', 401);
        }
    }

    /**
     * Handle user registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Register new user with email
            $user = $this->registerUser($request->input('email'));

            // Log the user in to establish session
            Auth::login($user);

            // Create response with user data only (no token in JSON)
            return $this->successResponse([
                'user' => $user
            ], 'Registration successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Invalidate the session
        auth()->guard('web')->logout();
        
        // Invalidate the session completely
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Create response with proper cookie clearing
        return $this->successResponse(null, 'Successfully logged out', 204);
    }

    /**
     * Authenticate user with username and password
     */
    private function authenticateUser(string $username, string $password): User
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            Log::warning('Failed login attempt - user not found', [
                'username' => $username,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            $validator = Validator::make([], []);
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.']
            ], $validator->errors());
        }

        if ($user->banned_at) {
            Log::warning('Failed login attempt - account suspended', [
                'user_id' => $user->id,
                'username' => $username,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Instead of throwing AccountSuspendedException, we'll throw a standard exception
            throw new \Exception('Your account has been suspended.');
        }

        if (!Hash::check($password, $user->password)) {
            Log::warning('Failed login attempt - invalid password', [
                'user_id' => $user->id,
                'username' => $username,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            $validator = Validator::make([], []);
            throw ValidationException::withMessages([
                'password' => ['The provided credentials are incorrect.']
            ], $validator->errors());
        }

        Log::info('Successful login', [
            'user_id' => $user->id,
            'username' => $username,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $user;
    }

    /**
     * Register a new user with email
     */
    private function registerUser(string $email): User
    {
        return DB::transaction(function () use ($email) {
            $username = $this->generateUniqueUsername(stristr($email, '@', true));
            $password = Str::random(12);

            $user = User::create([
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
                'referral_code' => $this->generateUniqueReferralCode(),
            ]);

            $user->wallet()->create();

            return $user;
        });
    }

    /**
     * Generate unique username
     */
    private function generateUniqueUsername(string $base): string
    {
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
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

    /**
     * Send password reset link
     */
    public function sendResetLink(SendResetLinkRequest $request): JsonResponse
    {
        // Implementation would go here
        return $this->successResponse(null, 'Password reset link sent');
    }

    /**
     * Reset user password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Implementation would go here
        return $this->successResponse(null, 'Password reset successfully');
    }
}