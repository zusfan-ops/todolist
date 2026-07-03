<?php

namespace App\Livewire;

use App\Models\WorkLog;
use App\Services\TimerService;
use Livewire\Attributes\On;
use Livewire\Component;

class TimerBar extends Component
{
    public ?WorkLog $activeTimer = null;

    public function mount(): void
    {
        $this->refreshTimer();
    }

    #[On('timer-changed')]
    public function refreshTimer(): void
    {
        $this->activeTimer = app(TimerService::class)->activeTimer(auth()->user())?->load('task');
    }

    public function stop(): void
    {
        if (! $this->activeTimer) {
            return;
        }

        $stopped = app(TimerService::class)->stop($this->activeTimer);
        $title = $this->activeTimer->task->title;
        $this->activeTimer = null;

        $this->dispatch('timer-changed');
        $this->dispatch('toast', message: "Tercatat: {$title} ({$stopped->duration_minutes}m)");
    }

    public function render()
    {
        return view('livewire.timer-bar');
    }
}
