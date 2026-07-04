<?php

namespace App\Livewire\Analytics;

use App\Models\Task;
use App\Models\WorkLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $period = 'weekly';

    public function render()
    {
        $tz = auth()->user()->displayTimezone();
        $projectIds = auth()->user()->accessibleProjects()->pluck('id');

        if ($this->period === 'weekly') {
            $weeks = collect();
            for ($i = 4; $i >= 0; $i--) {
                $start = now($tz)->subWeeks($i)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                $minutes = WorkLog::query()
                    ->where('user_id', auth()->id())
                    ->whereBetween('started_at', [$start, $end])
                    ->sum('duration_minutes');
                $completed = Task::query()
                    ->whereIn('project_id', $projectIds)
                    ->whereBetween('completed_at', [$start, $end])
                    ->count();
                $weeks->push([
                    'label' => $i === 0 ? 'Minggu ini' : $start->translatedFormat('j M'),
                    'minutes' => (int) $minutes,
                    'completed' => $completed,
                ]);
            }

            $weeklyTotal = WorkLog::query()
                ->where('user_id', auth()->id())
                ->whereBetween('started_at', [now($tz)->startOfWeek(), now($tz)->endOfWeek()])
                ->sum('duration_minutes');

            return view('livewire.analytics.index', [
                'weeks' => $weeks,
                'weeklyTotal' => $weeklyTotal,
                'monthlyMinutes' => null,
                'monthlyCompleted' => null,
                'projectBreakdown' => null,
            ]);
        }

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $start = now($tz)->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $minutes = WorkLog::query()
                ->where('user_id', auth()->id())
                ->whereBetween('started_at', [$start, $end])
                ->sum('duration_minutes');
            $completed = Task::query()
                ->whereIn('project_id', $projectIds)
                ->whereBetween('completed_at', [$start, $end])
                ->count();
            $months->push([
                'label' => $i === 0 ? 'Bulan ini' : $start->translatedFormat('M'),
                'minutes' => (int) $minutes,
                'completed' => $completed,
            ]);
        }

        $projectBreakdown = Task::query()
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [now($tz)->startOfMonth(), now($tz)->endOfMonth()])
            ->get()
            ->groupBy('project.name')
            ->map(fn ($tasks, $name) => [
                'name' => $name,
                'completed' => $tasks->count(),
            ])
            ->values();

        return view('livewire.analytics.index', [
            'weeks' => null,
            'weeklyTotal' => null,
            'monthlyMinutes' => $months,
            'monthlyCompleted' => null,
            'projectBreakdown' => $projectBreakdown,
        ]);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }
}