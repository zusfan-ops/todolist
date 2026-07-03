<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTask(Project $project, string $columnSlug): Task
    {
        $column = $project->kanbanColumns()->where('slug', $columnSlug)->firstOrFail();

        return $project->tasks()->create([
            'kanban_column_id' => $column->id,
            'title' => 'Task',
            'position' => Task::nextPositionIn($column->id),
            'client_uuid' => (string) Str::uuid(),
        ]);
    }

    public function test_task_without_checklist_uses_column_fallback_progress(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);

        $task = $this->makeTask($project, 'doing');

        $this->assertEquals(25, app(ProgressService::class)->forTask($task));
    }

    public function test_task_with_mixed_checklist_computes_percentage(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);

        $task = $this->makeTask($project, 'backlog');
        $task->checklistItems()->create(['body' => 'a', 'is_done' => true, 'position' => 1000, 'client_uuid' => (string) Str::uuid()]);
        $task->checklistItems()->create(['body' => 'b', 'is_done' => false, 'position' => 2000, 'client_uuid' => (string) Str::uuid()]);

        $this->assertEquals(50, app(ProgressService::class)->forTask($task->fresh()));
    }

    public function test_task_in_done_column_is_always_100(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);

        $task = $this->makeTask($project, 'done');
        $task->checklistItems()->create(['body' => 'a', 'is_done' => false, 'position' => 1000, 'client_uuid' => (string) Str::uuid()]);

        $this->assertEquals(100, app(ProgressService::class)->forTask($task->fresh()));
    }
}
