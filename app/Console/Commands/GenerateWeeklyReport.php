<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WeeklyReportReady;
use App\Services\WeeklyReportService;
use Illuminate\Console\Command;

class GenerateWeeklyReport extends Command
{
    protected $signature = 'kerjaku:generate-weekly-report';

    protected $description = 'Generate last week\'s PDF report and notify — runs Monday 06.00 WITA, see WORKFLOW.md §7';

    public function handle(WeeklyReportService $reports): int
    {
        foreach (User::all() as $user) {
            $weekParam = now($user->displayTimezone())->subWeek()->format('o-\WW');

            $reports->generatePdf($user, $weekParam);

            if (! empty(config('webpush.vapid.public_key'))) {
                $user->notify(new WeeklyReportReady);
            }
        }

        $this->info('Weekly reports generated.');

        return self::SUCCESS;
    }
}
