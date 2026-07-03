<?php

namespace App\Models;

use App\Exceptions\ChecklistIncompleteException;
use App\Services\ProgressService;
use App\Services\TimerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'kanban_column_id',
        'title',
        'description',
        'priority',
        'due_date',
        'estimate_minutes',
        'position',
        'progress_cached',
        'completed_at',
        'client_uuid',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function kanbanColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TaskPhoto::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest('created_at');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now($this->displayTimezone())->toDateString());
    }

    public function displayTimezone(): string
    {
        return config('kerjaku.display_timezone', 'Asia/Makassar');
    }

    public function progress(): int
    {
        return app(ProgressService::class)->forTask($this);
    }

    public function recalculateProgress(): void
    {
        app(ProgressService::class)->recalculate($this);
    }

    /**
     * State machine guard for kanban transitions. Handles WIP awareness (caller
     * decides on the warning), checklist-incomplete confirmation, auto-stopping
     * an active timer, and completed_at bookkeeping — see WORKFLOW.md §1.
     *
     * @return array{stopped_timer: ?WorkLog}
     *
     * @throws ChecklistIncompleteException
     */
    public function moveTo(KanbanColumn $column, ?int $position = null, bool $force = false): array
    {
        return DB::transaction(function () use ($column, $position, $force) {
            $fromColumn = $this->kanbanColumn;
            $stoppedTimer = null;
            $completedWithIncomplete = false;

            if ($column->is_done_column && $this->progress() < 100) {
                if (! $force) {
                    throw new ChecklistIncompleteException($this);
                }
                $completedWithIncomplete = true;
            }

            $this->kanban_column_id = $column->id;
            $this->position = $position ?? static::nextPositionIn($column->id);

            if ($column->is_done_column) {
                $this->progress_cached = 100;
                $this->completed_at = now();

                $activeTimer = $this->workLogs()->active()->first();
                if ($activeTimer) {
                    $stoppedTimer = app(TimerService::class)->stop($activeTimer);
                }
            } else {
                $this->completed_at = null;
            }

            $this->save();

            if (! $column->is_done_column) {
                $this->recalculateProgress();
            }

            static::renumberColumnIfNeeded($column->id);

            $this->activities()->create([
                'user_id' => $this->project->user_id,
                'event' => $completedWithIncomplete ? 'completed_with_incomplete_checklist' : 'moved',
                'meta' => ['from' => $fromColumn?->slug, 'to' => $column->slug],
            ]);

            return ['stopped_timer' => $stoppedTimer];
        });
    }

    public static function nextPositionIn(int $kanbanColumnId): int
    {
        return (int) (static::where('kanban_column_id', $kanbanColumnId)->max('position') ?? 0) + 1000;
    }

    /**
     * Gap-based reorder: renumber the column in steps of 1000 whenever two
     * adjacent tasks end up with a gap smaller than 2 — see DATABASE.md §4.
     */
    public static function renumberColumnIfNeeded(int $kanbanColumnId): void
    {
        $tasks = static::where('kanban_column_id', $kanbanColumnId)
            ->orderBy('position')
            ->get(['id', 'position']);

        $needsRenumber = false;
        $previous = null;

        foreach ($tasks as $task) {
            if ($previous !== null && ($task->position - $previous) < 2) {
                $needsRenumber = true;
                break;
            }
            $previous = $task->position;
        }

        if (! $needsRenumber) {
            return;
        }

        DB::transaction(function () use ($tasks) {
            $position = 1000;
            foreach ($tasks as $task) {
                static::whereKey($task->id)->update(['position' => $position]);
                $position += 1000;
            }
        });
    }
}
