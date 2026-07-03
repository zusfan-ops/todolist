<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WeeklyReportReady extends Notification
{
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Laporan mingguan siap')
            ->icon('/icons/icon-192.png')
            ->body('Laporan minggu lalu siap dilihat.')
            ->data(['url' => '/log']);
    }
}
