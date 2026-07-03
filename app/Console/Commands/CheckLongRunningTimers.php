<?php

namespace App\Console\Commands;

use App\Models\WorkLog;
use App\Notifications\LongRunningTimerReminder;
use Illuminate\Console\Command;

class CheckLongRunningTimers extends Command
{
    protected $signature = 'kerjaku:check-long-running-timers';

    protected $description = 'Push a reminder for timers running longer than 4 hours — see WORKFLOW.md §2';

    public function handle(): int
    {
        if (empty(config('webpush.vapid.public_key'))) {
            $this->warn('VAPID keys not configured — skipping push reminders.');

            return self::SUCCESS;
        }

        $timers = WorkLog::query()
            ->active()
            ->where('started_at', '<=', now()->subHours(4))
            ->with('user')
            ->get();

        foreach ($timers as $workLog) {
            $workLog->user->notify(new LongRunningTimerReminder($workLog));
        }

        return self::SUCCESS;
    }
}
