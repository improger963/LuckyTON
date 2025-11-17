<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SetPinRequest;
use App\Http\Requests\Api\TogglePinRequest;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    use ApiResponser;

    /**
     * Get current user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['wallet', 'socialAccounts']);
        return $this->successResponse($user, 'User profile fetched successfully');
    }

    /**
     * Set PIN code for user
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if PIN is already set
            if (!empty($user->pin_code)) {
                return $this->error('PIN code is already set. Use change PIN instead.', 400);
            }
            
            // Hash and store the PIN
            $user->update([
                'pin_code' => Hash::make($request->pin),
                'is_pin_enabled' => true, // Enable PIN by default when setting it
            ]);

            return $this->success(null, 'PIN code set successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Toggle PIN requirement for withdrawals
     */
    public function togglePin(TogglePinRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if PIN is set
            if (empty($user->pin_code)) {
                return $this->error('PIN code is not set. Please set a PIN first.', 400);
            }
            
            // Update PIN enabled status
            $user->update([
                'is_pin_enabled' => $request->enabled,
            ]);

            $message = $request->enabled
                ? 'PIN requirement has been enabled.'
                : 'PIN requirement has been disabled.';

            return $this->success(null, $message);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error('Current password is incorrect.', 400);
            }
            
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return $this->success(null, 'Your password has been changed successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get security status
     */
    public function getSecurityStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $securityStatus = [
            'has_password' => !empty($user->password),
            'has_pin' => !empty($user->pin_code),
            'pin_enabled' => $user->is_pin_enabled,
            'email_verified' => !empty($user->email_verified_at),
        ];

        return $this->success($securityStatus);
    }
}