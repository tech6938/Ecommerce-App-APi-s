<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    public static function sendOtp($phone, $otp)
    {
        $sid    = config('services.twilio.sid');
        $token  = config('services.twilio.token');
        $from   = config('services.twilio.from');

        // dd( $sid , $token , $from );

        $client = new Client($sid, $token);

        $client->messages->create($phone, [
            'from' => $from,
            'body' => "Your OTP is: $otp",
        ]);
    }
}
