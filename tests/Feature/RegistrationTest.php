<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_creates_a_user_with_a_starter_project_and_logs_in(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('today'));

        $user = User::where('email', 'budi@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertEquals(1, $user->projects()->count());
        $this->assertEquals(4, $user->projects()->first()->kanbanColumns()->count());
    }

    public function test_registering_with_a_taken_email_fails_validation(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(Register::class)
            ->set('name', 'Budi Santoso')
            ->set('email', 'taken@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);
    }
}
