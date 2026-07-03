<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KanbanColumnResource;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class KanbanColumnController extends Controller
{
    public function index(Request $request, Project $project)
    {
        abort_unless($project->user_id === $request->user()->id, 403);

        return KanbanColumnResource::collection($project->kanbanColumns);
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->user_id === $request->user()->id, 403);

        if ($project->kanbanColumns()->count() >= 6) {
            return response()->json([
                'message' => 'Maksimal 6 kolom per proyek',
                'errors' => ['columns' => ['limit_reached']],
            ], 422);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $maxPosition = $project->kanbanColumns()->max('position') ?? 0;

        $column = $project->kanbanColumns()->create([
            'name' => $data['name'],
            'slug' => \Illuminate\Support\Str::slug($data['name']),
            'position' => $maxPosition + 1000,
            'wip_limit' => $data['wip_limit'] ?? null,
        ]);

        return (new KanbanColumnResource($column))->response()->setStatusCode(201);
    }

    public function update(Request $request, KanbanColumn $column)
    {
        abort_unless($column->project->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'wip_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'position' => ['sometimes', 'integer'],
        ]);

        $column->update($data);

        return new KanbanColumnResource($column);
    }

    public function destroy(Request $request, KanbanColumn $column)
    {
        abort_unless($column->project->user_id === $request->user()->id, 403);

        $taskCount = $column->tasks()->count();

        if ($taskCount > 0) {
            $data = $request->validate([
                'migrate_to_column_id' => ['required', 'exists:kanban_columns,id'],
            ]);

            Task::where('kanban_column_id', $column->id)->update(['kanban_column_id' => $data['migrate_to_column_id']]);
        }

        $column->delete();

        return response()->json(null, 204);
    }
}
