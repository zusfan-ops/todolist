<?php

namespace App\Services;

use App\Models\TaskPhoto;
use App\Models\User;
use App\Models\WorkLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class WeeklyReportService
{
    public function build(User $user, string $weekParam): array
    {
        [$year, $week] = sscanf($weekParam, '%d-W%d');
        $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $logs = WorkLog::query()
            ->where('user_id', $user->id)
            ->whereNotNull('duration_minutes')
            ->whereBetween('started_at', [$start, $end])
            ->with('task.project')
            ->get();

        $byProject = $logs->groupBy('task.project.name')->map(fn ($group, $projectName) => [
            'project' => $projectName,
            'minutes' => $group->sum('duration_minutes'),
            'tasks_completed' => $group->pluck('task_id')->unique()->count(),
        ])->values();

        $photosCount = TaskPhoto::query()
            ->whereHas('task.project', fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'week' => $weekParam,
            'week_range' => [$start->toDateString(), $end->toDateString()],
            'total_minutes' => $logs->sum('duration_minutes'),
            'by_project' => $byProject,
            'photos_count' => $photosCount,
        ];
    }

    public function generatePdf(User $user, string $weekParam): string
    {
        $filename = "reports/{$weekParam}.pdf";

        if (! Storage::disk('public')->exists($filename)) {
            $pdf = Pdf::loadView('reports.weekly', $this->build($user, $weekParam));
            Storage::disk('public')->put($filename, $pdf->output());
        }

        return $filename;
    }
}
