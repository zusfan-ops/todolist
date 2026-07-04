<?php

namespace App\Livewire\Log;

use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WorkLogList extends Component
{
    public bool $showManualForm = false;

    public ?int $taskId = null;

    public string $date;

    public string $durationHours = '';

    public string $durationMinutes = '';

    public string $note = '';

    public function mount(): void
    {
        $this->date = now(auth()->user()->displayTimezone())->toDateString();
    }

    public function openManualForm(): void
    {
        $this->showManualForm = true;
    }

    public function saveManual(): void
    {
        $this->validate([
            'taskId' => ['required', 'exists:tasks,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'durationHours' => ['nullable', 'integer', 'min:0', 'max:16'],
            'durationMinutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $totalMinutes = ((int) ($this->durationHours ?: 0) * 60) + (int) ($this->durationMinutes ?: 0);

        if ($totalMinutes < 1 || $totalMinutes > 960) {
            $this->addError('durationMinutes', 'Durasi harus antara 1 menit dan 16 jam.');

            return;
        }

        WorkLog::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'started_at' => Carbon::parse($this->date, auth()->user()->displayTimezone())->utc(),
            'duration_minutes' => $totalMinutes,
            'note' => $this->note ?: null,
            'source' => 'manual',
            'client_uuid' => (string) Str::uuid(),
        ]);

        $this->reset(['taskId', 'durationHours', 'durationMinutes', 'note', 'showManualForm']);
        $this->date = now(auth()->user()->displayTimezone())->toDateString();

        $this->dispatch('toast', message: 'Log tersimpan');
    }

    #[On('timer-changed')]
    public function refresh(): void
    {
        //
    }

    public function render()
    {
        $tz = auth()->user()->displayTimezone();
        $now = now($tz);
        $startOfWeek = $now->copy()->startOfWeek();

        $weeklyMinutes = array_fill(0, 7, 0);
        $weekLogs = WorkLog::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('duration_minutes')
            ->where('started_at', '>=', $startOfWeek->copy()->utc())
            ->get();

        foreach ($weekLogs as $log) {
            $dayIndex = $log->started_at->copy()->setTimezone($tz)->dayOfWeekIso - 1;
            $weeklyMinutes[$dayIndex] += $log->duration_minutes;
        }

        $logs = WorkLog::query()
            ->with('task')
            ->where('user_id', auth()->id())
            ->whereNotNull('ended_at')
            ->orWhere(fn ($q) => $q->where('user_id', auth()->id())->where('source', 'manual'))
            ->orderByDesc('started_at')
            ->limit(30)
            ->get();

        $tasks = Task::query()
            ->whereIn('project_id', auth()->user()->accessibleProjects()->pluck('id'))
            ->orderBy('title')
            ->get();

        return view('livewire.log.work-log-list', [
            'weeklyMinutes' => $weeklyMinutes,
            'weeklyTotal' => array_sum($weeklyMinutes),
            'logs' => $logs,
            'tasks' => $tasks,
        ]);
    }
}
