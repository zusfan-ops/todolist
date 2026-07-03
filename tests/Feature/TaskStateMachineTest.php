<?php

namespace Tests\Feature;

use App\Exceptions\ChecklistIncompleteException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_moving_to_done_column_with_incomplete_checklist_requires_force(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();
        $done = $project->kanbanColumns()->where('slug', 'done')->first();

        $task = $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'T', 'position' => 1000, 'client_uuid' => (string) Str::uuid(),
        ]);
        $task->checklistItems()->create(['body' => 'a', 'is_done' => false, 'position' => 1000, 'client_uuid' => (string) Str::uuid()]);

        $this->expectException(ChecklistIncompleteException::class);
        $task->moveTo($done);
    }

    public function test_moving_to_done_column_with_force_sets_progress_100_and_completed_at(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();
        $done = $project->kanbanColumns()->where('slug', 'done')->first();

        $task = $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'T', 'position' => 1000, 'client_uuid' => (string) Str::uuid(),
        ]);
        $task->checklistItems()->create(['body' => 'a', 'is_done' => false, 'position' => 1000, 'client_uuid' => (string) Str::uuid()]);

        $task->moveTo($done, null, true);
        $task->refresh();

        $this->assertEquals(100, $task->progress_cached);
        $this->assertNotNull($task->completed_at);
        $this->assertEquals($done->id, $task->kanban_column_id);
    }

    public function test_moving_back_from_done_resets_completed_at_and_recalculates_progress(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();
        $doing = $project->kanbanColumns()->where('slug', 'doing')->first();
        $done = $project->kanbanColumns()->where('slug', 'done')->first();

        $task = $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'T', 'position' => 1000, 'client_uuid' => (string) Str::uuid(),
        ]);
        $task->checklistItems()->create(['body' => 'a', 'is_done' => true, 'position' => 1000, 'client_uuid' => (string) Str::uuid()]);
        $task->checklistItems()->create(['body' => 'b', 'is_done' => false, 'position' => 2000, 'client_uuid' => (string) Str::uuid()]);

        $task->moveTo($done, null, true);
        $task->moveTo($doing);
        $task->refresh();

        $this->assertNull($task->completed_at);
        $this->assertEquals(50, $task->progress_cached);
    }
}
