<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\User;
use App\Notifications\OtpEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'registered',
        ]);

        // Generate and send OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        OTP::create([
            'email' => $request->email,
            'otp' => $otpCode,
            'type' => 'email_verification',
            'is_used' => false,
            'expires_at' => now()->addMinutes(10)
        ]);

        try {
            $user->notify(new OtpEmailNotification($otpCode, 'verification'));
            Log::info('OTP sent to: ' . $user->email);
            $otpSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            $otpSent = false;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'data' => [
                'token' => $token,
                'user' => $user,
                'otp_sent' => $otpSent
            ]
        ], 200);
    }

    /**
     * Login user with email and password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to find user by email
        $user = User::where('email', $request->email)
            ->where('user_type', 'registered')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in',
                'requires_verification' => true,
                'email' => $user->email
            ], 403);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ], 200);
    }

    /**
     * Login with phone number (password or OTP)
     */
    public function loginWithPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by phone
        $user = User::where('phone', $request->phone)
                    ->where('user_type', 'registered')
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this phone number'
            ], 404);
        }

        // Delete old tokens and create new one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ], 200);
    }

    /**
     * Login as guest user
     */
    public function guestLoginOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find or create guest user by device ID
            $user = User::firstOrCreate(
                ['device_id' => $request->device_id, 'user_type' => 'guest'],
                [
                    'name' => $request->name ?? 'Guest User',
                    'guest_id' => 'guest_' . uniqid() . '_' . time(),
                    'email' => null,
                    'phone' => null,
                    'password' => null,
                ]
            );

            // Update name if provided and user is new or name is default
            if ($request->name && ($user->name === 'Guest User' || $user->wasRecentlyCreated)) {
                $user->update(['name' => $request->name]);
            }

            // Create token for guest
            $token = $user->createToken('guest_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => $user->wasRecentlyCreated ? 'Guest login successful' : 'Existing guest user logged in',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                    'is_guest' => true
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process guest login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert guest user to registered user
     */
    public function convertGuestToRegistered(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'required|string|max:20|unique:users,phone,' . Auth::id(),
            'password' => 'required|string',
            'name' => 'required|string|max:255',
            'device_id' => 'sometimes|string', // Optional, will keep existing device_id if not provided
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!$user->isGuest()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a guest user'
            ], 400);
        }

        // Update guest user to registered user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'registered',
            'guest_id' => null,
            // device_id remains the same
        ]);

        // Delete old tokens and create new one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Guest account converted to registered user successfully',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ], 200);
    }

    /**
     * Get user profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $updateData = [];

        // Update name if provided
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        // Update avatar if provided
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_url) {
                // Extract just the filename (remove any path)
                $oldFilename = basename($user->avatar_url);
                $oldAvatarPath = public_path('avatar/' . $oldFilename);

                // Debug: Log the path being checked
                Log::info('Deleting old avatar: ' . $oldAvatarPath);

                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                    Log::info('Old avatar deleted successfully');
                } else {
                    Log::warning('Old avatar not found: ' . $oldAvatarPath);
                }
            }

            // Upload new avatar
            $avatar = $request->file('avatar');
            $filename = 'avatar_' . time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('avatar'), $filename);

            // Store ONLY the filename (not the path)
            $updateData['avatar_url'] = $filename;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url ?? null,
                'updated_at' => $user->updated_at,
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * FCM Token
     */
    public function fcm_token(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $fcm = Auth::user();

        $fcm->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully',
        ]);
    }
}
