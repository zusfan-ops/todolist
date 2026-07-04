<?php

namespace App\Livewire;

use App\Exceptions\ActiveTimerConflictException;
use App\Models\ChecklistItem;
use App\Models\Task;
use App\Services\TimerService;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskDetailSheet extends Component
{
    public ?int $taskId = null;

    public string $newChecklistBody = '';

    #[On('open-task')]
    public function openModal(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->reset(['newChecklistBody']);
    }

    public function close(): void
    {
        $this->taskId = null;
    }

    public function toggleChecklist(int $itemId): void
    {
        $item = ChecklistItem::whereHas('task', fn ($q) => $q->whereHas('project', fn ($q2) => $q2->where('user_id', auth()->id())))
            ->findOrFail($itemId);

        $item->update([
            'is_done' => ! $item->is_done,
            'done_at' => ! $item->is_done ? now() : null,
        ]);

        $this->dispatch('task-updated');
    }

    public function addChecklistItem(): void
    {
        $this->validate(['newChecklistBody' => ['required', 'string', 'max:300']]);

        $task = $this->task();
        $task->checklistItems()->create([
            'body' => $this->newChecklistBody,
            'position' => ChecklistItem::where('task_id', $task->id)->max('position') + 1000,
            'client_uuid' => (string) Str::uuid(),
        ]);

        $this->newChecklistBody = '';
        $this->dispatch('task-updated');
    }

    public function startTimer(bool $force = false): void
    {
        $task = $this->task();

        try {
            app(TimerService::class)->start(auth()->user(), $task, $force);
        } catch (ActiveTimerConflictException $e) {
            $this->dispatch('confirm-start-timer', message: 'Timer lain sedang berjalan pada "'.$e->activeTimer->task->title.'". Hentikan dan mulai yang baru?');

            return;
        }

        // Close the sheet so the persistent timer bar (top of the layout) is
        // reachable — it's covered by this sheet's own backdrop while open.
        $this->taskId = null;

        $this->dispatch('timer-changed');
        $this->dispatch('toast', message: 'Timer dimulai ⏱');
    }

    public function deleteTask(): void
    {
        $task = $this->task();
        $title = $task->title;
        $task->delete();

        $this->taskId = null;

        $this->dispatch('task-updated');
        $this->dispatch('toast', message: "Task \"{$title}\" dihapus");
    }

    private function task(): Task
    {
        return Task::whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['project', 'kanbanColumn', 'checklistItems', 'activities', 'photos', 'workLogs'])
            ->findOrFail($this->taskId);
    }

    #[On('task-updated')]
    public function refresh(): void
    {
        //
    }

    public function render()
    {
        return view('livewire.task-detail-sheet', [
            'task' => $this->taskId ? $this->task() : null,
        ]);
    }
}
