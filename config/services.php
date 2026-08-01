<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_PHONE'),
    ],

    'nita' => [
        'api_key' => 'sWwFI9Y86LG29sZeh4VoqC_d_AmG_lGQvF8OROZ_7q0',
        'username' => 'ABDOUL_KGB',
        'password' => 'KGB_XPRESS@2026',
        'authenticate_url' => 'https://sandbox.nitapiservices.com/api/authenticate',
        'check_status_url' => 'https://sandbox.nitapiservices.com/api/nitaServices/achatEnLigne/checkAchatStatus',
        'long_transaction' => '2.0301',
        'lat_transaction' => '13.5123',
        'ip_address' => '127.0.0.1',
    ],

];
