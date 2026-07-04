<?php

namespace Tests\Feature;

use App\Livewire\Kanban\Board;
use App\Livewire\Staff\AcceptInvite;
use App\Livewire\Staff\Index as StaffIndex;
use App\Livewire\TaskDetailSheet;
use App\Models\Project;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Coverage for the owner/staff multi-user model: staff can only reach
 * projects they've been explicitly assigned to, cannot perform structural
 * actions (create/delete project, delete task, view reports), and the
 * invite-link join flow behaves correctly.
 */
class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeProjectWithTask(User $owner, string $name = 'P'): array
    {
        $project = $owner->projects()->create([
            'name' => $name, 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();
        $task = $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'Task in '.$name, 'position' => 1000,
            'client_uuid' => (string) Str::uuid(),
        ]);

        return [$project, $task];
    }

    private function makeStaff(User $owner): User
    {
        return User::factory()->create(['owner_id' => $owner->id]);
    }

    public function test_staff_can_see_only_assigned_project_on_kanban_board(): void
    {
        $owner = User::factory()->create();
        [$assigned] = $this->makeProjectWithTask($owner, 'Assigned');
        [$unassigned] = $this->makeProjectWithTask($owner, 'Unassigned');

        $staff = $this->makeStaff($owner);
        $assigned->staffMembers()->attach($staff->id);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->assertSee('Assigned')
            ->assertDontSee('Unassigned');
    }

    public function test_staff_cannot_select_unassigned_project(): void
    {
        $owner = User::factory()->create();
        [$assigned] = $this->makeProjectWithTask($owner, 'Assigned');
        [$unassigned] = $this->makeProjectWithTask($owner, 'Unassigned');

        $staff = $this->makeStaff($owner);
        $assigned->staffMembers()->attach($staff->id);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->assertSet('activeProjectId', $assigned->id)
            ->call('selectProject', $unassigned->id)
            ->assertSet('activeProjectId', $assigned->id);
    }

    public function test_staff_cannot_move_task_in_unassigned_project(): void
    {
        $owner = User::factory()->create();
        [$assigned] = $this->makeProjectWithTask($owner, 'Assigned');
        [$unassigned, $taskB] = $this->makeProjectWithTask($owner, 'Unassigned');
        $doneB = $unassigned->kanbanColumns()->where('slug', 'done')->first();

        $staff = $this->makeStaff($owner);
        $assigned->staffMembers()->attach($staff->id);

        try {
            Livewire::actingAs($staff)
                ->test(Board::class)
                ->call('moveTask', $taskB->id, $doneB->id, null, true);
            $this->fail('Expected a ModelNotFoundException for an unassigned project task.');
        } catch (ModelNotFoundException) {
            // expected
        }

        $this->assertNotEquals($doneB->id, $taskB->fresh()->kanban_column_id);
    }

    public function test_staff_can_toggle_checklist_on_assigned_project_task(): void
    {
        $owner = User::factory()->create();
        [$project, $task] = $this->makeProjectWithTask($owner);
        $item = $task->checklistItems()->create([
            'body' => 'Item', 'position' => 1000, 'client_uuid' => (string) Str::uuid(),
        ]);

        $staff = $this->makeStaff($owner);
        $project->staffMembers()->attach($staff->id);

        Livewire::actingAs($staff)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $task->id)
            ->call('toggleChecklist', $item->id);

        $this->assertTrue($item->fresh()->is_done);
    }

    public function test_staff_cannot_delete_task_in_assigned_project(): void
    {
        $owner = User::factory()->create();
        [$project, $task] = $this->makeProjectWithTask($owner);

        $staff = $this->makeStaff($owner);
        $project->staffMembers()->attach($staff->id);

        Livewire::actingAs($staff)
            ->test(TaskDetailSheet::class)
            ->call('openModal', $task->id)
            ->call('deleteTask')
            ->assertForbidden();

        $this->assertModelExists($task);
    }

    public function test_staff_cannot_create_project(): void
    {
        $owner = User::factory()->create();
        $staff = $this->makeStaff($owner);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->set('newProjectName', 'Sneaky project')
            ->call('createProject')
            ->assertForbidden();

        $this->assertDatabaseMissing('projects', ['name' => 'Sneaky project']);
    }

    public function test_staff_cannot_view_weekly_report_api(): void
    {
        $owner = User::factory()->create();
        $staff = $this->makeStaff($owner);

        $this->actingAs($staff)
            ->getJson('/api/reports/weekly')
            ->assertForbidden();
    }

    public function test_staff_cannot_open_staff_management_page(): void
    {
        $owner = User::factory()->create();
        $staff = $this->makeStaff($owner);

        Livewire::actingAs($staff)->test(StaffIndex::class)->assertForbidden();
    }

    public function test_owner_can_invite_assign_and_remove_staff(): void
    {
        $owner = User::factory()->create();
        [$project] = $this->makeProjectWithTask($owner);

        $component = Livewire::actingAs($owner)
            ->test(StaffIndex::class)
            ->set('inviteName', 'Budi')
            ->set('inviteEmail', 'budi@example.com')
            ->call('invite');

        $invitation = StaffInvitation::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame($owner->id, $invitation->owner_id);
        $component->assertSet('generatedLink', route('invite.accept', $invitation->token));

        // Accept the invitation as a fresh guest.
        Livewire::test(AcceptInvite::class, ['token' => $invitation->token])
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('accept');

        $staff = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame($owner->id, $staff->owner_id);
        $this->assertTrue($staff->isStaff());
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertAuthenticatedAs($staff);

        // Owner assigns the project to the new staff member.
        Livewire::actingAs($owner)
            ->test(StaffIndex::class)
            ->call('toggleAssignment', $staff->id, $project->id);

        $this->assertTrue($staff->assignedProjects()->whereKey($project->id)->exists());

        // Owner removes the staff member entirely.
        Livewire::actingAs($owner)
            ->test(StaffIndex::class)
            ->call('removeStaff', $staff->id);

        $this->assertModelMissing($staff);
    }

    public function test_owner_cannot_assign_another_owners_staff_or_project(): void
    {
        $ownerA = User::factory()->create();
        [$projectA] = $this->makeProjectWithTask($ownerA);
        $ownerB = User::factory()->create();
        $staffB = $this->makeStaff($ownerB);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($ownerA)
            ->test(StaffIndex::class)
            ->call('toggleAssignment', $staffB->id, $projectA->id);
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $owner = User::factory()->create();
        $invitation = $owner->staffInvitations()->create([
            'name' => 'Expired',
            'email' => 'expired@example.com',
            'token' => StaffInvitation::generateToken(),
            'expires_at' => now()->subDay(),
        ]);

        Livewire::test(AcceptInvite::class, ['token' => $invitation->token])
            ->assertSet('invalid', true);

        $this->assertDatabaseMissing('users', ['email' => 'expired@example.com']);
    }

    public function test_owner_project_creation_still_works(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)
            ->test(Board::class)
            ->set('newProjectName', 'New Project')
            ->set('newProjectColor', '#2A6DD6')
            ->call('createProject');

        $this->assertDatabaseHas('projects', ['name' => 'New Project', 'user_id' => $owner->id]);
    }
}
