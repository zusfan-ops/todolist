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
        $this->activeProjectId = auth()->user()->projects()->active()->orderBy('position')->value('id');
    }

    public function openNewProjectModal(): void
    {
        $this->reset(['newProjectName', 'newProjectColor']);
        $this->newProjectColor = self::COLOR_PALETTE[0];
        $this->showNewProjectModal = true;
    }

    public function createProject(): void
    {
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
        $owned = auth()->user()->projects()->whereKey($projectId)->exists();

        if ($owned) {
            $this->activeProjectId = $projectId;
        }
    }

    public function moveTask(int $taskId, int $toColumnId, ?int $position = null, bool $force = false): void
    {
        $task = Task::whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))->findOrFail($taskId);
        $column = KanbanColumn::whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))->findOrFail($toColumnId);

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
        $projects = auth()->user()->projects()->active()->orderBy('position')->get();

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
