<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">Hello {{ $name }}!</h1>

        <p>Your OTP for {{ $type === 'verification' ? 'email verification' : 'password reset' }} is:</p>

        <div style="text-align: center; margin: 30px 0;">
            <h2 style="font-size: 24px; font-weight: bold; text-align: center; padding: 15px; background: #f4f4f4; border-radius: 5px; display: inline-block; min-width: 120px;">
                {{ $otp }}
            </h2>
        </div>

        <p>This OTP will expire in 10 minutes.</p>

        <p>If you did not request this, no further action is required.</p>

        <p style="margin-top: 30px;">Regards,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
