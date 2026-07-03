<?php

namespace Tests\Feature;

use App\Livewire\Kanban\Board;
use App\Livewire\TaskDetailSheet;
use App\Livewire\Today\Dashboard;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the cross-tenant IDOR bugs found when this app
 * moved from single-user to multi-user SaaS: several Livewire components
 * queried/mutated by raw ID without checking the record belonged to the
 * authenticated user's own projects.
 */
class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithTask(string $columnSlug = 'backlog'): array
    {
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $column = $project->kanbanColumns()->where('slug', $columnSlug)->first();
        $task = $project->tasks()->create([
            'kanban_column_id' => $column->id, 'title' => 'Secret task', 'position' => 1000,
            'client_uuid' => (string) Str::uuid(),
        ]);

        return [$user, $project, $task];
    }

    public function test_task_detail_sheet_cannot_load_another_users_task(): void
    {
        [$ownerA, , $taskA] = $this->makeUserWithTask();
        $userB = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($userB)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $taskA->id);
    }

    public function test_kanban_board_cannot_move_another_users_task(): void
    {
        [$ownerA, $projectA, $taskA] = $this->makeUserWithTask();
        $doneA = $projectA->kanbanColumns()->where('slug', 'done')->first();
        $userB = User::factory()->create();

        try {
            Livewire::actingAs($userB)
                ->test(Board::class)
                ->call('moveTask', $taskA->id, $doneA->id, null, true);
            $this->fail('Expected a ModelNotFoundException for a cross-tenant task.');
        } catch (ModelNotFoundException) {
            // expected
        }

        $this->assertNotEquals($doneA->id, $taskA->fresh()->kanban_column_id);
    }

    public function test_kanban_board_cannot_select_another_users_project(): void
    {
        [$ownerA, $projectA] = $this->makeUserWithTask();
        $userB = User::factory()->create();
        $projectB = $userB->projects()->create(['name' => 'B project', 'color' => '#111111', 'position' => 0, 'client_uuid' => (string) Str::uuid()]);

        Livewire::actingAs($userB)
            ->test(Board::class)
            ->assertSet('activeProjectId', $projectB->id)
            ->call('selectProject', $projectA->id)
            ->assertSet('activeProjectId', $projectB->id);
    }

    public function test_today_dashboard_does_not_leak_other_tenants_tasks(): void
    {
        [$ownerA, , $taskA] = $this->makeUserWithTask('doing');
        $taskA->update(['due_date' => today()]);
        $userB = User::factory()->create();

        Livewire::actingAs($userB)
            ->test(Dashboard::class)
            ->assertDontSee('Secret task');
    }
}
