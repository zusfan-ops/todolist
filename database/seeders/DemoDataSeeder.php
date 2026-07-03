<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'zusfan.mashuri@gmail.com')->firstOrFail();

        if ($user->projects()->exists()) {
            return;
        }

        $projects = [
            ['name' => 'SwaMart', 'color' => '#2A6DD6', 'icon' => 'store'],
            ['name' => 'Cak Goto', 'color' => '#7A4F2B', 'icon' => 'coffee'],
            ['name' => 'Rimba Aviary', 'color' => '#2F9E6E', 'icon' => 'bird'],
        ];

        $created = [];
        foreach ($projects as $i => $data) {
            $created[] = $user->projects()->create([
                ...$data,
                'position' => $i,
                'client_uuid' => (string) Str::uuid(),
            ]);
        }

        [$swaMart, $cakGoto, $aviary] = $created;

        $doing = fn (Project $p) => $p->kanbanColumns()->where('slug', 'doing')->first();
        $backlog = fn (Project $p) => $p->kanbanColumns()->where('slug', 'backlog')->first();
        $review = fn (Project $p) => $p->kanbanColumns()->where('slug', 'review')->first();
        $done = fn (Project $p) => $p->kanbanColumns()->where('slug', 'done')->first();

        $task1 = $this->makeTask($swaMart, $doing($swaMart), 'Integrasi Midtrans sandbox', 'high', today());
        $this->checklist($task1, ['Setup server key' => true, 'Callback handler' => true, 'Test QRIS' => false, 'Test kartu' => false]);

        $task2 = $this->makeTask($aviary, $doing($aviary), 'Pasang rangka aviary sisi timur', 'urgent', today());
        $this->checklist($task2, ['Ukur & potong hollow' => true, 'Las rangka bawah' => true, 'Las rangka atas' => true, 'Cat dasar anti karat' => false]);

        $task3 = $this->makeTask($cakGoto, $backlog($cakGoto), 'Foto produk roasting batch Juli', 'normal', null);
        $this->checklist($task3, ['Siapkan properti' => false, 'Foto 5 varian' => false]);

        $task4 = $this->makeTask($swaMart, $review($swaMart), 'Skema tabel orders + channel', 'normal', null);
        $this->checklist($task4, ['Migration' => true, 'Seeder' => true]);

        $task5 = $this->makeTask($cakGoto, $done($cakGoto), 'Restock kemasan 250g', 'low', null);
        $this->checklist($task5, ['Order supplier' => true, 'Terima & cek' => true]);
        $task5->update(['completed_at' => now()->subDay()]);
        $task5->recalculateProgress();

        $task6 = $this->makeTask($swaMart, $backlog($swaMart), 'Uji GPS gating radius 100m', 'high', today());

        $user->workLogs()->create([
            'task_id' => $task1->id,
            'started_at' => now()->subHours(2)->subMinutes(25),
            'ended_at' => now(),
            'duration_minutes' => 145,
            'source' => 'timer',
            'client_uuid' => (string) Str::uuid(),
        ]);

        $user->workLogs()->create([
            'task_id' => $task2->id,
            'started_at' => now()->subDay()->setTime(14, 0),
            'ended_at' => now()->subDay()->setTime(17, 0),
            'duration_minutes' => 180,
            'note' => 'las rangka',
            'source' => 'manual',
            'client_uuid' => (string) Str::uuid(),
        ]);
    }

    private function makeTask(Project $project, $column, string $title, string $priority, ?\DateTimeInterface $dueDate): Task
    {
        return $project->tasks()->create([
            'kanban_column_id' => $column->id,
            'title' => $title,
            'priority' => $priority,
            'due_date' => $dueDate,
            'position' => Task::nextPositionIn($column->id),
            'client_uuid' => (string) Str::uuid(),
        ]);
    }

    private function checklist(Task $task, array $items): void
    {
        $position = 1000;
        foreach ($items as $body => $isDone) {
            $task->checklistItems()->create([
                'body' => $body,
                'is_done' => $isDone,
                'done_at' => $isDone ? now() : null,
                'position' => $position,
                'client_uuid' => (string) Str::uuid(),
            ]);
            $position += 1000;
        }
    }
}
