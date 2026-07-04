<?php

namespace App\Livewire\Kanban;

use App\Exceptions\ChecklistIncompleteException;
use App\Models\KanbanColumn;
use App\Models\Task;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Board extends Component
{
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

    public function moveTask(int $taskId, int $toColumnId, ?int $position = null, bool $force = false): void
    {
        $projectIds = auth()->user()->accessibleProjects()->pluck('id');

        $task = Task::whereIn('project_id', $projectIds)->findOrFail($taskId);
        $column = KanbanColumn::whereIn('project_id', $projectIds)->findOrFail($toColumnId);

        try {
            $result = $task->moveTo($column, $position, $force);
        } catch (ChecklistIncompleteException $e) {
            $this->dispatch('confirm-move', taskId: $taskId, toColumnId: $toColumnId, position: $position, message: $e->getMessage());

            return;
        }

        if ($result['stopped_timer']) {
            $this->dispatch('timer-changed');
        }

        if ($column->is_done_column) {
            $this->dispatch('toast', message: 'Selesai ✔');
        }
    }

    #[On('task-updated')]
    public function refresh(): void
    {
        //
    }

    public function render()
    {
        $projects = auth()->user()->accessibleProjects()->active()->orderBy('position')->get();

        // activeProjectId is a public Livewire property — a client could set it
        // directly in the request payload, so ownership is re-checked here too
        // (defense in depth, same pattern as the checklist/timer guards).
        $columns = collect();
        if ($this->activeProjectId && $projects->contains('id', $this->activeProjectId)) {
            $columns = KanbanColumn::query()
                ->where('project_id', $this->activeProjectId)
                ->orderBy('position')
                ->with(['tasks' => fn ($q) => $q->with(['project', 'checklistItems'])->orderBy('position')])
                ->get();
        }

        return view('livewire.kanban.board', [
            'projects' => $projects,
            'columns' => $columns,
        ]);
    }
}
