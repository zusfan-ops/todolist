<?php

namespace App\Observers;

use App\Models\ChecklistItem;

class ChecklistItemObserver
{
    public function saved(ChecklistItem $item): void
    {
        $task = $item->task()->withTrashed()->first();
        if (! $task) return;

        $task->recalculateProgress();

        if ($item->is_done && ($item->wasRecentlyCreated || $item->wasChanged('is_done'))) {
            $task->activities()->create([
                'user_id' => auth()->id() ?? $task->project->user_id,
                'event' => 'checklist_done',
                'meta' => ['body' => $item->body],
            ]);
        }
    }

    public function deleted(ChecklistItem $item): void
    {
        $task = $item->task()->withTrashed()->first();
        if ($task) {
            $task->recalculateProgress();
        }
    }
}
