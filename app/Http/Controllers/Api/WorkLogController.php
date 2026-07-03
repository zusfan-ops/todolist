<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ActiveTimerConflictException;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorkLogResource;
use App\Models\Task;
use App\Models\WorkLog;
use App\Services\TimerService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class WorkLogController extends Controller
{
    public function active(Request $request)
    {
        $timer = app(TimerService::class)->activeTimer($request->user());

        return response()->json(['data' => $timer ? new WorkLogResource($timer) : null]);
    }

    public function start(Request $request, Task $task)
    {
        abort_unless($task->project->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'client_uuid' => ['sometimes', 'uuid'],
            'force' => ['sometimes', 'boolean'],
        ]);

        try {
            $workLog = app(TimerService::class)->start(
                $request->user(),
                $task,
                $data['force'] ?? false,
                $data['client_uuid'] ?? null
            );
        } catch (ActiveTimerConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'meta' => [
                    'active_timer' => [
                        'task_id' => $e->activeTimer->task_id,
                        'task_title' => $e->activeTimer->task->title,
                    ],
                ],
            ], 409);
        }

        return (new WorkLogResource($workLog))->response()->setStatusCode(201);
    }

    public function stop(Request $request, WorkLog $workLog)
    {
        abort_unless($workLog->user_id === $request->user()->id, 403);

        $workLog = app(TimerService::class)->stop($workLog);

        return new WorkLogResource($workLog);
    }

    public function storeManual(Request $request, Task $task)
    {
        abort_unless($task->project->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:960'],
            'note' => ['nullable', 'string', 'max:1000'],
            'client_uuid' => ['required', 'uuid'],
        ]);

        $startedAt = Carbon::parse($data['started_at']);
        if ($startedAt->isAfter(now()->addMinutes(5))) {
            $startedAt = now();
        }

        $workLog = WorkLog::firstOrCreate(
            ['client_uuid' => $data['client_uuid']],
            [
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'started_at' => $startedAt,
                'duration_minutes' => $data['duration_minutes'],
                'note' => $data['note'] ?? null,
                'source' => 'manual',
            ]
        );

        return (new WorkLogResource($workLog))->response()->setStatusCode(201);
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $logs = WorkLog::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('duration_minutes')
            ->when($data['from'] ?? null, fn ($q, $from) => $q->whereDate('started_at', '>=', $from))
            ->when($data['to'] ?? null, fn ($q, $to) => $q->whereDate('started_at', '<=', $to))
            ->when($data['project_id'] ?? null, fn ($q, $pid) => $q->whereHas('task', fn ($t) => $t->where('project_id', $pid)))
            ->with('task')
            ->orderByDesc('started_at')
            ->get()
            ->groupBy(fn ($log) => $log->started_at->copy()->setTimezone(config('kerjaku.display_timezone'))->toDateString());

        return response()->json([
            'data' => $logs->map(fn ($group) => WorkLogResource::collection($group))->values(),
            'meta' => ['days' => $logs->keys()],
        ]);
    }
}
