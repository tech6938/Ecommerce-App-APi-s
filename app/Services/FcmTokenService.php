<?php

namespace App\Services;

use Log;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Exception\MessagingException;

class FcmTokenService
{
    protected Messaging $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/fcm.json'));
        $this->messaging = $factory->createMessaging();
    }

    // Send to single device
    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        try {
            $message = [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data,
            ];

            return $this->messaging->send($message);
        } catch (MessagingException $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }

    // Send to multiple devices
    public function sendToMultiple($tokens, $title, $body, $data = [])
    {
        try {
            $message = [
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data,
            ];

            return $this->messaging->sendMulticast($message, $tokens);
        } catch (MessagingException $e) {
            Log::error('FCM Multicast Error: ' . $e->getMessage());
            return false;
        }
    }

    // Send to all users (commonly used for announcements)
    public function sendToAllUsers($title, $body, $data = [])
    {
        $tokens = User::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (!empty($tokens)) {
            return $this->sendToMultiple($tokens, $title, $body, $data);
        }

        return false;
    }

    // Specific notification for new courses
    public function notifyNewCourse($course)
    {
        return $this->sendToAllUsers(
            'New Course Available!',
            'Check out the new course: ' . $course->title,
            [
                'course_id' => (string)$course->id,
                'type' => 'new_course',
                'title' => $course->title,
                'price' => (string)$course->price,
                'action' => 'view_course'
            ]
        );
    }
}
