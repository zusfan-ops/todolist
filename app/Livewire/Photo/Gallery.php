<?php

namespace App\Livewire\Photo;

use App\Models\Task;
use App\Models\TaskPhoto;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Gallery extends Component
{
    public ?string $filterType = null;

    #[On('task-updated')]
    public function refresh(): void
    {
        //
    }

    public function render()
    {
        $projectIds = auth()->user()->accessibleProjects()->pluck('id');

        $photos = TaskPhoto::query()
            ->whereHas('task', fn ($q) => $q->whereIn('project_id', $projectIds))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->with('task')
            ->latest()
            ->limit(60)
            ->get();

        $tasks = Task::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->orderBy('title')
            ->get();

        return view('livewire.photo.gallery', [
            'photos' => $photos,
            'tasks' => $tasks,
        ]);
    }
}
