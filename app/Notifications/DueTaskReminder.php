<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DueTaskReminder extends Notification
{
    public function __construct(private int $count, private bool $isTomorrow = false)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $title = $this->isTomorrow ? 'Besok' : 'Jatuh tempo hari ini';
        $body = $this->isTomorrow
            ? "Besok: {$this->count} task"
            : "{$this->count} task jatuh tempo hari ini";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/icons/icon-192.png')
            ->body($body)
            ->data(['url' => '/app']);
    }
}
