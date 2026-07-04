<?php

namespace App\Livewire\Todo;

use App\Models\SimpleTodo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $newBody = '';

    public ?int $activeProjectId = null;

    public bool $showNewProjectModal = false;

    public string $newProjectName = '';

    public string $newProjectColor = '#2A6DD6';

    public const COLOR_PALETTE = ['#2A6DD6', '#7A4F2B', '#2F9E6E', '#D6482F', '#F5A300', '#6D5FD1'];

    public function mount(): void
    {
        $this->activeProjectId = auth()->user()->accessibleProjects()->active()->orderBy('position')->value('id');
    }

    public function openNewProjectModal(): void
    {
        abort_if(auth()->user()->isStaff(), 403);

        $this->reset(['newProjectName', 'newProjectColor']);
        $this->newProjectColor = self::COLOR_PALETTE[0];
        $this->showNewProjectModal = true;
    }

    public function createProject(): void
    {
        abort_if(auth()->user()->isStaff(), 403);

        $this->validate([
            'newProjectName' => ['required', 'string', 'max:100'],
            'newProjectColor' => ['required', 'string', 'size:7'],
        ]);

        $position = auth()->user()->projects()->count();

        $project = auth()->user()->projects()->create([
            'name' => $this->newProjectName,
            'color' => $this->newProjectColor,
            'position' => $position,
            'client_uuid' => (string) Str::uuid(),
        ]);

        $this->showNewProjectModal = false;
        $this->activeProjectId = $project->id;

        $this->dispatch('toast', message: 'Proyek dibuat');
    }

    public function selectProject(int $projectId): void
    {
        $accessible = auth()->user()->accessibleProjects()->whereKey($projectId)->exists();

        if ($accessible) {
            $this->activeProjectId = $projectId;
        }
    }

    public function deleteProject(int $projectId): void
    {
        $project = auth()->user()->accessibleProjects()->findOrFail($projectId);

        abort_unless(auth()->user()->canManageProject($project), 403);

        $project->delete();

        $this->activeProjectId = auth()->user()->accessibleProjects()->active()->orderBy('position')->value('id');

        $this->dispatch('toast', message: 'Proyek dihapus');
    }

    public function add(): void
    {
        $this->validate(['newBody' => ['required', 'string', 'max:300']]);

        $position = (int) (auth()->user()->simpleTodos()->max('position') ?? 0) + 1;

        auth()->user()->simpleTodos()->create([
            'body' => $this->newBody,
            'position' => $position,
        ]);

        $this->newBody = '';
    }

    public function toggle(int $id): void
    {
        $todo = auth()->user()->simpleTodos()->findOrFail($id);

        $todo->update(['is_done' => ! $todo->is_done]);
    }

    public function delete(int $id): void
    {
        auth()->user()->simpleTodos()->findOrFail($id)->delete();
    }

    public function clearCompleted(): void
    {
        auth()->user()->simpleTodos()->where('is_done', true)->delete();
    }

    public function render()
    {
        $todos = auth()->user()->simpleTodos()->orderBy('position')->get();
        $projects = auth()->user()->accessibleProjects()->active()->orderBy('position')->get();

        return view('livewire.todo.index', [
            'todos' => $todos,
            'doneCount' => $todos->where('is_done', true)->count(),
            'projects' => $projects,
        ]);
    }
}
