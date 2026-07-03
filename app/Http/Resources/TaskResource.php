<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'estimate_minutes' => $this->estimate_minutes,
            'kanban_column' => $this->kanbanColumn?->slug,
            'kanban_column_id' => $this->kanban_column_id,
            'position' => $this->position,
            'progress' => $this->progress_cached,
            'checklist_total' => $this->checklistItems->count(),
            'checklist_done' => $this->checklistItems->where('is_done', true)->count(),
            'has_active_timer' => (bool) $this->workLogs->where('ended_at', null)->count(),
            'photo_count' => $this->photos_count ?? $this->photos()->count(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
