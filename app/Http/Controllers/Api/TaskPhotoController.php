<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskPhotoResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskPhoto;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use RuntimeException;

class TaskPhotoController extends Controller
{
    public function store(Request $request, Task $task)
    {
        abort_unless($request->user()->canAccessProject($task->project), 403);

        $data = $request->validate([
            'file' => ['required', 'image', 'max:5120'],
            'type' => ['required', 'in:before,progress,after,proof'],
            'sha256' => ['required', 'string', 'size:64'],
            'caption' => ['nullable', 'string', 'max:300'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'taken_at' => ['nullable', 'date'],
            'client_uuid' => ['required', 'uuid'],
        ]);

        try {
            $photo = app(PhotoService::class)->store($task, $data['file'], $data);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'Verifikasi integritas foto gagal',
                'errors' => ['sha256' => ['mismatch']],
            ], 422);
        }

        $task->activities()->create([
            'user_id' => $request->user()->id,
            'event' => 'photo_added',
            'meta' => ['type' => $photo->type],
        ]);

        return (new TaskPhotoResource($photo))->response()->setStatusCode(201);
    }

    public function indexForTask(Request $request, Task $task)
    {
        abort_unless($request->user()->canAccessProject($task->project), 403);

        return TaskPhotoResource::collection($task->photos()->latest()->get());
    }

    public function indexForProject(Request $request, Project $project)
    {
        abort_unless($request->user()->canAccessProject($project), 403);

        $photos = TaskPhoto::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $project->id))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->get();

        return TaskPhotoResource::collection($photos);
    }

    public function destroy(Request $request, TaskPhoto $photo)
    {
        abort_unless($request->user()->canAccessProject($photo->task->project), 403);

        \Illuminate\Support\Facades\Storage::disk($photo->disk)->delete([$photo->path, $photo->thumb_path]);
        $photo->delete();

        return response()->json(null, 204);
    }
}
