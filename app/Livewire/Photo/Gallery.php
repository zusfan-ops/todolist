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
        $photos = TaskPhoto::query()
            ->whereHas('task.project', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->with('task')
            ->latest()
            ->limit(60)
            ->get();

        $tasks = Task::query()
            ->whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('title')
            ->get();

        return view('livewire.photo.gallery', [
            'photos' => $photos,
            'tasks' => $tasks,
        ]);
    }
}
