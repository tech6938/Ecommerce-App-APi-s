<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Verse;
use App\Services\FcmTokenService;
use Carbon\Carbon;

class SendFcmNotifications extends Command
{
    protected $signature = 'fcm:send';
    protected $description = 'Send FCM notifications to all users based on verse time_slot';

    protected FcmTokenService $fcmService;

    public function __construct(FcmTokenService $fcmService)
    {
        parent::__construct();
        $this->fcmService = $fcmService;
    }

    public function handle()
    {
        $this->info('Starting FCM notification process...');

        // Determine current time slot
        $currentHour = Carbon::now()->hour;
        $timeSlot = match (true) {
            $currentHour >= 5 && $currentHour < 12 => 'Morning',
            $currentHour >= 12 && $currentHour < 17 => 'Noon',
            $currentHour >= 17 && $currentHour < 22 => 'Evening',
            default => null
        };

        if (!$timeSlot) {
            $this->info('No time slot for sending notifications at this hour.');
            return 0;
        }

        // Fetch all active verses for the current time slot
        $verses = Verse::where('time_slot', $timeSlot)
            ->where('is_active', 1)
            ->get();

        if ($verses->isEmpty()) {
            $this->info('No active verses for this time slot.');
            return 0;
        }

        // Get all users with fcm_token
        $users = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        if (empty($users)) {
            $this->info('No users with FCM token found.');
            return 0;
        }

        foreach ($verses as $verse) {
            $title = "Verse Reminder";
            $body = $verse->preview ?? "Check out your scheduled verse!";

            $this->fcmService->sendToMultiple($users, $title, $body);
            $this->info("Sent notifications for verse ID {$verse->id} to " . count($users) . " users.");
        }

        $this->info('FCM notifications process completed.');
        return 0;
    }
}
