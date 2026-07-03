<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Task;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request, Task $task)
    {
        abort_unless($task->project->user_id === $request->user()->id, 403);

        return ActivityResource::collection($task->activities);
    }
}
