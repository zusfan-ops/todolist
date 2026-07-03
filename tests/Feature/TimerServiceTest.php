<?php

namespace Tests\Feature;

use App\Exceptions\ActiveTimerConflictException;
use App\Models\User;
use App\Services\TimerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TimerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTask(User $user)
    {
        $project = $user->projects()->create([
            'name' => 'P', 'color' => '#000000', 'position' => 0, 'client_uuid' => (string) Str::uuid(),
        ]);
        $backlog = $project->kanbanColumns()->where('slug', 'backlog')->first();

        return $project->tasks()->create([
            'kanban_column_id' => $backlog->id, 'title' => 'T', 'position' => 1000, 'client_uuid' => (string) Str::uuid(),
        ]);
    }

    public function test_cannot_start_second_timer_without_force(): void
    {
        $user = User::factory()->create();
        $taskA = $this->makeTask($user);
        $taskB = $this->makeTask($user);

        $service = app(TimerService::class);
        $service->start($user, $taskA);

        $this->expectException(ActiveTimerConflictException::class);
        $service->start($user, $taskB);
    }

    public function test_force_stops_old_timer_and_starts_new_one(): void
    {
        $user = User::factory()->create();
        $taskA = $this->makeTask($user);
        $taskB = $this->makeTask($user);

        $service = app(TimerService::class);
        $first = $service->start($user, $taskA);
        $second = $service->start($user, $taskB, force: true);

        $this->assertNotNull($first->fresh()->ended_at);
        $this->assertNull($second->fresh()->ended_at);
        $this->assertEquals($taskB->id, $service->activeTimer($user)->task_id);
    }

    public function test_stop_sets_duration_minutes(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTask($user);

        $service = app(TimerService::class);
        $workLog = $service->start($user, $task);
        $stopped = $service->stop($workLog);

        $this->assertNotNull($stopped->ended_at);
        $this->assertGreaterThanOrEqual(1, $stopped->duration_minutes);
    }
}
