<?php

namespace Tests\Feature;

use App\Livewire\Todo\Index;
use App\Models\SimpleTodo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SimpleTodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_todo(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('newBody', 'Beli galon')
            ->call('add')
            ->assertSet('newBody', '');

        $this->assertDatabaseHas('simple_todos', [
            'user_id' => $user->id,
            'body' => 'Beli galon',
            'is_done' => false,
        ]);
    }

    public function test_adding_a_todo_requires_a_body(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('newBody', '')
            ->call('add')
            ->assertHasErrors(['newBody']);
    }

    public function test_user_can_toggle_and_delete_own_todo(): void
    {
        $user = User::factory()->create();
        $todo = $user->simpleTodos()->create(['body' => 'Test', 'position' => 1]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('toggle', $todo->id);

        $this->assertTrue($todo->fresh()->is_done);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('delete', $todo->id);

        $this->assertModelMissing($todo);
    }

    public function test_clear_completed_only_removes_done_items_for_that_user(): void
    {
        $user = User::factory()->create();
        $done = $user->simpleTodos()->create(['body' => 'Done', 'position' => 1, 'is_done' => true]);
        $pending = $user->simpleTodos()->create(['body' => 'Pending', 'position' => 2, 'is_done' => false]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('clearCompleted');

        $this->assertModelMissing($done);
        $this->assertModelExists($pending);
    }

    public function test_user_cannot_toggle_another_users_todo(): void
    {
        $ownerA = User::factory()->create();
        $todoA = $ownerA->simpleTodos()->create(['body' => 'Secret', 'position' => 1]);
        $userB = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($userB)
            ->test(Index::class)
            ->call('toggle', $todoA->id);
    }

    public function test_user_cannot_delete_another_users_todo(): void
    {
        $ownerA = User::factory()->create();
        $todoA = $ownerA->simpleTodos()->create(['body' => 'Secret', 'position' => 1]);
        $userB = User::factory()->create();

        try {
            Livewire::actingAs($userB)
                ->test(Index::class)
                ->call('delete', $todoA->id);
            $this->fail('Expected a ModelNotFoundException for a cross-tenant todo.');
        } catch (ModelNotFoundException) {
            // expected
        }

        $this->assertModelExists($todoA);
    }
}
