<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KanbanColumnResource;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KanbanColumnController extends Controller
{
    public function index(Request $request, Project $project)
    {
        abort_unless($request->user()->canAccessProject($project), 403);

        return KanbanColumnResource::collection($project->kanbanColumns);
    }

    // Column structure (create/edit/delete) is owner-only — staff work
    // within the columns an owner has already set up.
    public function store(Request $request, Project $project)
    {
        abort_unless($request->user()->canManageProject($project), 403);

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
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while ($project->kanbanColumns()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $column = $project->kanbanColumns()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'position' => $maxPosition + 1000,
            'wip_limit' => $data['wip_limit'] ?? null,
        ]);

        return (new KanbanColumnResource($column))->response()->setStatusCode(201);
    }

    public function update(Request $request, KanbanColumn $column)
    {
        abort_unless($request->user()->canManageProject($column->project), 403);

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
        abort_unless($request->user()->canManageProject($column->project), 403);

        $taskCount = $column->tasks()->count();

        if ($taskCount > 0) {
            $data = $request->validate([
                'migrate_to_column_id' => ['required', 'exists:kanban_columns,id'],
            ]);

            abort_if((int) $data['migrate_to_column_id'] === (int) $column->id, 422, 'Cannot migrate tasks to the same column being deleted.');

            DB::transaction(function () use ($column, $data) {
                Task::where('kanban_column_id', $column->id)->update(['kanban_column_id' => $data['migrate_to_column_id']]);
                $column->delete();
            });
        } else {
            $column->delete();
        }

        return response()->json(null, 204);
    }
}
