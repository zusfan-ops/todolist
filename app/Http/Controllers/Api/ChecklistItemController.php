<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistItemResource;
use App\Models\ChecklistItem;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    public function store(Request $request, Task $task)
    {
        abort_unless($request->user()->canAccessProject($task->project), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:300'],
            'client_uuid' => ['required', 'uuid'],
        ]);

        $item = ChecklistItem::firstOrCreate(
            ['client_uuid' => $data['client_uuid']],
            [
                'task_id' => $task->id,
                'body' => $data['body'],
                'position' => (int) $task->checklistItems()->max('position') + 1000,
            ]
        );

        return (new ChecklistItemResource($item))
            ->additional(['meta' => ['task_progress' => $task->fresh()->progress_cached]])
            ->response()->setStatusCode(201);
    }

    public function update(Request $request, ChecklistItem $item)
    {
        abort_unless($request->user()->canAccessProject($item->task->project), 403);

        $data = $request->validate([
            'is_done' => ['required', 'boolean'],
        ]);

        $item->update([
            'is_done' => $data['is_done'],
            'done_at' => $data['is_done'] ? now() : null,
        ]);

        return (new ChecklistItemResource($item))
            ->additional(['meta' => ['task_progress' => $item->task->fresh()->progress_cached]]);
    }

    public function destroy(Request $request, ChecklistItem $item)
    {
        abort_unless($request->user()->canAccessProject($item->task->project), 403);

        $task = $item->task;
        $item->delete();
        $task->recalculateProgress();

        return response()->json(null, 204);
    }
}
