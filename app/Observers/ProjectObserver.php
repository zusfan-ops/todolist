<?php

namespace App\Observers;

use App\Models\KanbanColumn;
use App\Models\Project;

class ProjectObserver
{
    /**
     * Every project gets four default kanban columns — see WORKFLOW.md §5
     * for the fallback_progress values used when a task has no checklist.
     */
    public function created(Project $project): void
    {
        $columns = [
            ['name' => 'Backlog', 'slug' => 'backlog', 'position' => 1000, 'is_done_column' => false, 'fallback_progress' => 0],
            ['name' => 'Dikerjakan', 'slug' => 'doing', 'position' => 2000, 'is_done_column' => false, 'fallback_progress' => 25, 'wip_limit' => 3],
            ['name' => 'Review', 'slug' => 'review', 'position' => 3000, 'is_done_column' => false, 'fallback_progress' => 75],
            ['name' => 'Selesai', 'slug' => 'done', 'position' => 4000, 'is_done_column' => true, 'fallback_progress' => 100],
        ];

        foreach ($columns as $column) {
            $project->kanbanColumns()->create($column);
        }
    }
}
