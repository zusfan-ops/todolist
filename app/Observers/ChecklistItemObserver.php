<?php

namespace App\Observers;

use App\Models\ChecklistItem;

class ChecklistItemObserver
{
    public function saved(ChecklistItem $item): void
    {
        $item->task->recalculateProgress();

        if ($item->wasChanged('is_done') && $item->is_done) {
            $item->task->activities()->create([
                'user_id' => auth()->id() ?? $item->task->project->user_id,
                'event' => 'checklist_done',
                'meta' => ['body' => $item->body],
            ]);
        }
    }

    public function deleted(ChecklistItem $item): void
    {
        $item->task->recalculateProgress();
    }
}
