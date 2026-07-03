<?php

namespace App\Livewire\Today;

use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $tz = auth()->user()->displayTimezone();
        $today = Carbon::now($tz)->toDateString();

        $baseQuery = fn () => Task::query()
            ->with(['project', 'checklistItems'])
            ->whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereHas('kanbanColumn', fn ($q) => $q->where('is_done_column', false));

        $dueToday = $baseQuery()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $today)
            ->orderByRaw('due_date < ? desc', [$today])
            ->orderByRaw("case priority when 'urgent' then 1 when 'high' then 2 when 'normal' then 3 else 4 end")
            ->get();

        $inProgress = $baseQuery()
            ->whereHas('kanbanColumn', fn ($q) => $q->where('slug', 'doing'))
            ->get();

        $activeTimerTaskIds = WorkLog::query()->where('user_id', auth()->id())->active()->pluck('task_id');
        foreach ($dueToday->concat($inProgress) as $task) {
            $task->has_active_timer = $activeTimerTaskIds->contains($task->id);
        }

        $minutesToday = WorkLog::query()
            ->where('user_id', auth()->id())
            ->whereDate('started_at', $today)
            ->sum('duration_minutes');

        $activeTimer = WorkLog::query()->where('user_id', auth()->id())->active()->first();
        if ($activeTimer) {
            $minutesToday += now()->diffInMinutes($activeTimer->started_at);
        }

        $completedToday = Task::query()
            ->whereHas('project', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereDate('completed_at', $today)
            ->count();

        return view('livewire.today.dashboard', [
            'dueToday' => $dueToday,
            'inProgress' => $inProgress,
            'minutesToday' => (int) $minutesToday,
            'completedToday' => $completedToday,
        ]);
    }

    #[On('task-updated')]
    #[On('timer-changed')]
    public function refresh(): void
    {
        // render() re-queries automatically on next request/poll
    }
}
