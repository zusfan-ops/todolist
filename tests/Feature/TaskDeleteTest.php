<?php

namespace Tests\Feature;

use App\Livewire\TaskDetailSheet;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TaskDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeTask(User $user): Task
    {
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();

        return $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'Delete me', 'position' => 1000,
            'client_uuid' => (string) Str::uuid(),
        ]);
    }

    public function test_owner_can_delete_their_task(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTask($user);

        Livewire::actingAs($user)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $task->id)
            ->call('deleteTask')
            ->assertSet('taskId', null);

        $this->assertSoftDeleted($task);
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $ownerA = User::factory()->create();
        $task = $this->makeTask($ownerA);
        $userB = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($userB)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $task->id)
            ->call('deleteTask');
    }

    public function test_deleted_task_no_longer_appears_on_kanban_board(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTask($user);

        Livewire::actingAs($user)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $task->id)
            ->call('deleteTask');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Kanban\Board::class)
            ->assertDontSee('Delete me');
    }
}
