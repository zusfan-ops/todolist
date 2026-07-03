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
        $tz = config('kerjaku.display_timezone');
        $today = Carbon::now($tz)->toDateString();

        $baseQuery = fn () => Task::query()
            ->with(['project', 'checklistItems'])
            ->whereHas('kanbanColumn', fn ($q) => $q->where('is_done_column', false));

        $dueToday = $baseQuery()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $today)
            ->orderByRaw('due_date < ? desc', [$today])
            ->orderByRaw("field(priority,'urgent','high','normal','low')")
            ->get();

        $inProgress = $baseQuery()
            ->whereHas('kanbanColumn', fn ($q) => $q->where('slug', 'doing'))
            ->get();

        $activeTimerTaskIds = WorkLog::query()->active()->pluck('task_id');
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
