<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler jobs — see WORKFLOW.md §7. cPanel only needs one cron line:
// * * * * * php artisan schedule:run
// Trigger times use the default timezone; each command filters per-user by
// that user's own displayTimezone() once it runs (see SendDueReminders etc).
$tz = config('kerjaku.display_timezone');

Schedule::command('kerjaku:send-due-reminders')->dailyAt('07:00')->timezone($tz);
Schedule::command('kerjaku:send-due-reminders --tomorrow')->dailyAt('19:00')->timezone($tz);
Schedule::command('kerjaku:check-long-running-timers')->everyThirtyMinutes();
Schedule::command('kerjaku:generate-weekly-report')->weeklyOn(1, '06:00')->timezone($tz);
Schedule::command('kerjaku:prune-deleted-tasks')->daily();
