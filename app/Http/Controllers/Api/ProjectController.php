<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->accessibleProjects()->active()->withCount('tasks')->orderBy('position')->get();

        return ProjectResource::collection($projects);
    }

    public function store(Request $request)
    {
        // Staff work inside projects assigned to them by the owner — they
        // don't spin up new ones.
        abort_if($request->user()->isStaff(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'size:7'],
            'icon' => ['nullable', 'string', 'max:50'],
            'client_uuid' => ['required', 'uuid'],
        ]);

        $project = Project::firstOrCreate(
            ['client_uuid' => $data['client_uuid']],
            [
                'user_id' => $request->user()->id,
                'name' => $data['name'],
                'color' => $data['color'],
                'icon' => $data['icon'] ?? null,
                'position' => $request->user()->projects()->count(),
            ]
        );

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($request->user()->canManageProject($project), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['sometimes', 'string', 'size:7'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'in:active,archived'],
        ]);

        $project->update($data);

        return new ProjectResource($project);
    }
}
