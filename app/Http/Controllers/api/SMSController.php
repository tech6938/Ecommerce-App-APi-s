<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\SmsService;

class SMSController extends Controller
{
    public function sendOtp(Request $request)
    {
        try {
            $phone = $request->phone;
            // return $phone;
            $otp = rand(100000, 999999);

            // Store OTP for 5 minutes
            Cache::put('otp_' . $phone, $otp, now()->addMinutes(5));

            // Send SMS
            SmsService::sendOtp($phone, $otp);

            return response()->json(['status' => true, 'message' => 'OTP sent via SMS']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()]);
        }
    }

    // => Verify Method in Controller
    public function verifyOtp(Request $request)
    {
        $phone = $request->phone;
        $otp = $request->otp;

        $cachedOtp = Cache::get('otp_' . $phone);

        if ($cachedOtp && $cachedOtp == $otp) {
            Cache::forget('otp_' . $phone);
            return response()->json(['status' => true, 'message' => 'OTP verified']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid or expired OTP']);
    }
}
