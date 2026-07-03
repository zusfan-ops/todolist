<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_task_twice_with_same_client_uuid_is_idempotent(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $uuid = (string) Str::uuid();

        $payload = [
            'project_id' => $project->id,
            'title' => 'Idempotent task',
            'client_uuid' => $uuid,
        ];

        $first = $this->actingAs($user)->postJson('/api/tasks', $payload);
        $second = $this->actingAs($user)->postJson('/api/tasks', $payload);

        $first->assertStatus(201);
        $second->assertStatus(201);
        $this->assertEquals($first->json('data.id'), $second->json('data.id'));
        $this->assertEquals(1, Task::where('client_uuid', $uuid)->count());
    }

    public function test_move_to_done_column_returns_409_when_checklist_incomplete(): void
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

        $response = $this->actingAs($user)->postJson("/api/tasks/{$task->id}/move", [
            'to_column_id' => $done->id,
        ]);

        $response->assertStatus(409);

        $forced = $this->actingAs($user)->postJson("/api/tasks/{$task->id}/move", [
            'to_column_id' => $done->id,
            'force' => true,
        ]);

        $forced->assertStatus(200);
        $forced->assertJsonPath('data.progress', 100);
    }
}
