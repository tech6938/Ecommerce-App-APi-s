<?php

namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpEmailNotification extends Notification
{


    protected $otp;
    protected $type;

    public function __construct($otp, $type = 'verification')
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->type === 'verification' ? 'Email Verification OTP' : 'Password Reset OTP';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.otp-verification', [
                'name' => $notifiable->name,
                'otp' => $this->otp,
                'type' => $this->type,
                'subject' => $subject
            ]);
    }
}
