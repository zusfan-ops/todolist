<?php

namespace App\Services;

use App\Exceptions\ActiveTimerConflictException;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimerService
{
    public function activeTimer(User $user): ?WorkLog
    {
        return WorkLog::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * @throws ActiveTimerConflictException
     */
    public function start(User $user, Task $task, bool $force = false, ?string $clientUuid = null): WorkLog
    {
        return DB::transaction(function () use ($user, $task, $force, $clientUuid) {
            $existing = WorkLog::query()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->task_id === $task->id) {
                return $existing;
            }

            if ($existing && ! $force) {
                throw new ActiveTimerConflictException($existing);
            }

            if ($existing) {
                $this->stop($existing);
            }

            return WorkLog::firstOrCreate(
                ['client_uuid' => $clientUuid ?? (string) Str::uuid()],
                [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'started_at' => now(),
                    'source' => 'timer',
                ]
            );
        });
    }

    public function stop(WorkLog $workLog): WorkLog
    {
        return DB::transaction(function () use ($workLog) {
            $workLog = WorkLog::whereKey($workLog->id)->lockForUpdate()->firstOrFail();

            if (! $workLog->isRunning()) {
                return $workLog;
            }

            $endedAt = now();
            $minutes = max(1, (int) ceil($workLog->started_at->diffInSeconds($endedAt) / 60));

            $workLog->update([
                'ended_at' => $endedAt,
                'duration_minutes' => $minutes,
            ]);

            return $workLog;
        });
    }
}
