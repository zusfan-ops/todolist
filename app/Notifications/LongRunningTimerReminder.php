<?php

namespace App\Notifications;

use App\Models\WorkLog;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LongRunningTimerReminder extends Notification
{
    public function __construct(private WorkLog $workLog)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $tz = $this->workLog->user->displayTimezone();
        $startedAt = $this->workLog->started_at->copy()->setTimezone($tz)->format('H.i');

        return (new WebPushMessage)
            ->title('Timer masih berjalan')
            ->icon('/icons/icon-192.png')
            ->body("Timer masih berjalan sejak {$startedAt} — lupa stop?")
            ->data(['url' => '/app']);
    }
}
