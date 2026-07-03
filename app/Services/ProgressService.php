<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;

class ProgressService
{
    public function forTask(Task $task): int
    {
        if ($task->kanbanColumn?->is_done_column) {
            return 100;
        }

        $total = $task->checklistItems()->count();

        if ($total > 0) {
            $done = $task->checklistItems()->where('is_done', true)->count();

            return (int) round($done / $total * 100);
        }

        return (int) ($task->kanbanColumn?->fallback_progress ?? 0);
    }

    public function recalculate(Task $task): void
    {
        $task->progress_cached = $this->forTask($task);
        $task->saveQuietly();
    }

    public function forProject(Project $project): int
    {
        $avg = $project->tasks()->avg('progress_cached');

        return (int) round($avg ?? 0);
    }
}
