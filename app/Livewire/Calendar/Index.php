<?php

namespace App\Livewire\Calendar;

use App\Models\Task;
use App\Models\WorkLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public int $currentYear;
    public int $currentMonth;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $now = now();
        $this->currentYear = (int) $now->format('Y');
        $this->currentMonth = (int) $now->format('m');
        $this->selectedDate = $now->toDateString();
    }

    public function previousMonth(): void
    {
        $date = now()->setDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = (int) $date->format('Y');
        $this->currentMonth = (int) $date->format('m');
        $this->selectedDate = null;
    }

    public function nextMonth(): void
    {
        $date = now()->setDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = (int) $date->format('Y');
        $this->currentMonth = (int) $date->format('m');
        $this->selectedDate = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    public function render()
    {
        $projectIds = auth()->user()->accessibleProjects()->pluck('id');
        $startOfMonth = now()->setDate($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $tasks = Task::query()
            ->with(['project', 'kanbanColumn'])
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($t) => $t->due_date->toDateString());

        $startDay = (int) $startOfMonth->format('N');
        $daysInMonth = (int) $startOfMonth->format('t');

        $weeks = [];
        $day = 1;
        $col = $startDay - 1;
        while ($day <= $daysInMonth) {
            $week = array_fill(0, 7, null);
            while ($col < 7 && $day <= $daysInMonth) {
                $date = sprintf('%04d-%02d-%02d', $this->currentYear, $this->currentMonth, $day);
                $week[$col] = [
                    'day' => $day,
                    'date' => $date,
                    'tasks' => $tasks->get($date, collect()),
                    'isToday' => $date === now()->toDateString(),
                ];
                $day++;
                $col++;
            }
            $weeks[] = $week;
            $col = 0;
        }

        if (! $this->selectedDate || ! isset($tasks[$this->selectedDate])) {
            $selectedDateTasks = collect();
        } else {
            $selectedDateTasks = $tasks[$this->selectedDate];
        }

        $activeTimerTaskIds = WorkLog::query()->where('user_id', auth()->id())->active()->pluck('task_id');
        foreach ($selectedDateTasks as $task) {
            $task->has_active_timer = $activeTimerTaskIds->contains($task->id);
        }

        $monthName = now()->setDate($this->currentYear, $this->currentMonth, 1)->translatedFormat('F Y');

        return view('livewire.calendar.index', [
            'weeks' => $weeks,
            'selectedDateTasks' => $selectedDateTasks,
            'monthName' => $monthName,
        ]);
    }

    #[On('task-updated')]
    #[On('timer-changed')]
    public function refresh(): void {}
}