<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\User;
use App\Notifications\OtpEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    /**
     * Send OTP for email verification
     */
    public function sendVerificationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Delete existing unused OTPs for this email
        OTP::where('email', $request->email)
            ->where('type', 'email_verification')
            ->where('is_used', false)
            ->delete();

        // Generate 6-digit OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        // Store OTP in database
        $otp = OTP::create([
            'email' => $request->email,
            'otp' => $otpCode,
            'type' => 'email_verification',
            'is_used' => false,
            'expires_at' => now()->addMinutes(10) // Expires in 10 minutes
        ]);

        // Send OTP via email
        try {
            $user->notify(new OtpEmailNotification($otpCode, 'verification'));

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'data' => [
                    'email' => $request->email,
                    'expires_in' => 10 // minutes
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());

            // Delete the OTP record if email fails
            $otp->delete();

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the OTP record
        $otpRecord = OTP::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('type', 'email_verification')
            ->where('is_used', false)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Check if OTP is expired
        if (!$otpRecord->isValid()) {
            $otpRecord->delete();
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // Mark OTP as used
        $otpRecord->markAsUsed();

        $user = User::where('email', $request->email)->first();
        $user->markEmailAsVerified();

        // Delete all used/expired OTPs for this email
        OTP::where('email', $request->email)
            ->where('type', 'email_verification')
            ->delete();

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ], 200);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Delete old OTPs
        OTP::where('email', $request->email)
            ->where('type', 'email_verification')
            ->delete();

        // Generate new OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        $otp = OTP::create([
            'email' => $request->email,
            'otp' => $otpCode,
            'type' => 'email_verification',
            'is_used' => false,
            'expires_at' => now()->addMinutes(10)
        ]);

        try {
            $user->notify(new OtpEmailNotification($otpCode, 'verification'));

            return response()->json([
                'success' => true,
                'message' => 'OTP resent successfully'
            ], 200);
        } catch (\Exception $e) {
            $otp->delete();
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP'
            ], 500);
        }
    }
}
