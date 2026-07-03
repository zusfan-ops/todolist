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
        $weekParam = now(config('kerjaku.display_timezone'))->subWeek()->format('o-\WW');

        foreach (User::all() as $user) {
            $reports->generatePdf($user, $weekParam);

            if (! empty(config('webpush.vapid.public_key'))) {
                $user->notify(new WeeklyReportReady);
            }
        }

        $this->info("Weekly report generated for {$weekParam}.");

        return self::SUCCESS;
    }
}
