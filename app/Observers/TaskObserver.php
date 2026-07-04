<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\TimerService;

class TaskObserver
{
    public function created(Task $task): void
    {
        $task->activities()->create([
            'user_id' => auth()->id() ?? $task->project->user_id,
            'event' => 'created',
            'meta' => null,
        ]);
    }

    /**
     * A task can be deleted while its timer is running — auto-stop it so the
     * work log entry still reflects real elapsed time. See WORKFLOW.md §8.
     */
    public function deleting(Task $task): void
    {
        $activeTimer = $task->workLogs()->active()->first();

        if ($activeTimer) {
            app(TimerService::class)->stop($activeTimer);
        }
    }
}
