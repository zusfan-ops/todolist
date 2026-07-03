<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\DueTaskReminder;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'kerjaku:send-due-reminders {--tomorrow : Send the H-1 reminder instead of due-today}';

    protected $description = 'Push a reminder for tasks due today (07.00 WITA) or tomorrow (19.00 WITA) — see WORKFLOW.md §7';

    public function handle(): int
    {
        if (empty(config('webpush.vapid.public_key'))) {
            $this->warn('VAPID keys not configured — skipping push reminders.');

            return self::SUCCESS;
        }

        $tomorrow = $this->option('tomorrow');
        $tz = config('kerjaku.display_timezone');
        $targetDate = $tomorrow ? now($tz)->addDay()->toDateString() : now($tz)->toDateString();

        foreach (User::all() as $user) {
            $count = Task::query()
                ->whereHas('project', fn ($q) => $q->where('user_id', $user->id))
                ->whereHas('kanbanColumn', fn ($q) => $q->where('is_done_column', false))
                ->whereDate('due_date', $targetDate)
                ->count();

            if ($count > 0) {
                $user->notify(new DueTaskReminder($count, $tomorrow));
            }
        }

        return self::SUCCESS;
    }
}
