<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class PruneDeletedTasks extends Command
{
    protected $signature = 'kerjaku:prune-deleted-tasks';

    protected $description = 'Permanently delete tasks that were soft-deleted more than 30 days ago — see DATABASE.md §10';

    public function handle(): int
    {
        $count = Task::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get()
            ->each->forceDelete()
            ->count();

        $this->info("Purged {$count} task(s) soft-deleted more than 30 days ago.");

        return self::SUCCESS;
    }
}
