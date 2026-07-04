<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChecklistIncompleteException;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, Project $project)
    {
        abort_unless($request->user()->canAccessProject($project), 403);

        $tasks = $project->tasks()
            ->with(['kanbanColumn', 'checklistItems', 'workLogs'])
            ->withCount('photos')
            ->when($request->query('column'), fn ($q, $slug) => $q->whereHas('kanbanColumn', fn ($c) => $c->where('slug', $slug)))
            ->orderBy('position')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function today(Request $request)
    {
        $tz = $request->user()->displayTimezone();
        $today = now($tz)->toDateString();
        $projectIds = $request->user()->accessibleProjects()->pluck('id');

        $baseQuery = fn () => Task::query()
            ->whereIn('project_id', $projectIds)
            ->whereHas('kanbanColumn', fn ($q) => $q->where('is_done_column', false))
            ->with(['kanbanColumn', 'checklistItems', 'workLogs'])
            ->withCount('photos');

        $dueToday = $baseQuery()->whereDate('due_date', '<=', $today)->get();
        $inProgress = $baseQuery()->whereHas('kanbanColumn', fn ($q) => $q->where('slug', 'doing'))->get();

        $activeTimer = WorkLog::query()->where('user_id', $request->user()->id)->active()->first();

        $minutesToday = WorkLog::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('started_at', $today)
            ->sum('duration_minutes');

        $completedToday = Task::query()
            ->whereIn('project_id', $projectIds)
            ->whereDate('completed_at', $today)
            ->count();

        return response()->json([
            'data' => [
                'due_today' => TaskResource::collection($dueToday),
                'in_progress' => TaskResource::collection($inProgress),
                'active_timer' => $activeTimer ? [
                    'task_id' => $activeTimer->task_id,
                    'started_at' => $activeTimer->started_at->toIso8601String(),
                ] : null,
                'summary' => [
                    'minutes_today' => (int) $minutesToday,
                    'completed_today' => $completedToday,
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'client_uuid' => ['required', 'uuid'],
        ]);

        $project = Project::findOrFail($data['project_id']);
        abort_unless($request->user()->canAccessProject($project), 403);

        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->firstOrFail();

        $task = Task::firstOrCreate(
            ['client_uuid' => $data['client_uuid']],
            [
                'project_id' => $project->id,
                'kanban_column_id' => $backlog->id,
                'title' => $data['title'],
                'priority' => $data['priority'] ?? 'normal',
                'due_date' => $data['due_date'] ?? null,
                'position' => Task::nextPositionIn($backlog->id),
            ]
        );

        return (new TaskResource($task->fresh(['kanbanColumn', 'checklistItems', 'workLogs'])))->response()->setStatusCode(201);
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($request->user()->canAccessProject($task->project), 403);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'estimate_minutes' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $task->update($data);

        return new TaskResource($task->load(['kanbanColumn', 'checklistItems', 'workLogs']));
    }

    public function move(Request $request, Task $task)
    {
        abort_unless($request->user()->canAccessProject($task->project), 403);

        $data = $request->validate([
            'to_column_id' => ['required', 'exists:kanban_columns,id'],
            'position' => ['nullable', 'integer'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $column = KanbanColumn::findOrFail($data['to_column_id']);
        abort_unless($column->project_id === $task->project_id, 403);

        try {
            $result = $task->moveTo($column, $data['position'] ?? null, $data['force'] ?? false);
        } catch (ChecklistIncompleteException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['checklist' => ['incomplete']],
            ], 409);
        }

        return (new TaskResource($task->fresh(['kanbanColumn', 'checklistItems', 'workLogs'])))
            ->additional([
                'meta' => [
                    'stopped_timer' => $result['stopped_timer'] ? [
                        'id' => $result['stopped_timer']->id,
                        'duration_minutes' => $result['stopped_timer']->duration_minutes,
                    ] : null,
                ],
            ]);
    }

    // Deleting a task is a structural/destructive action — owner-only.
    public function destroy(Request $request, Task $task)
    {
        abort_unless($request->user()->canManageProject($task->project), 403);

        $task->delete();

        return response()->json(null, 204);
    }
}
