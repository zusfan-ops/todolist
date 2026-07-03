<div wire:poll.30s="refreshTimer">
    @if ($activeTimer)
        <div x-data="{
                startedAt: new Date('{{ $activeTimer->started_at->toIso8601String() }}').getTime(),
                tick: 0,
                get display() {
                    this.tick;
                    const s = Math.max(0, Math.floor((Date.now() - this.startedAt) / 1000));
                    const p = n => String(n).padStart(2, '0');
                    return p(Math.floor(s / 3600)) + ':' + p(Math.floor(s % 3600 / 60)) + ':' + p(s % 60);
                }
             }"
             x-init="setInterval(() => tick++, 1000)"
             class="bg-ink-700 text-white px-5 py-2.5 flex items-center justify-between shrink-0 border-b-2 border-vest-500">
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-wider text-vest-500 font-disp font-bold">Timer berjalan</p>
                <p class="text-sm truncate">{{ $activeTimer->task->title }}</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="font-mono font-bold text-lg tabular-nums" x-text="display"></span>
                <button wire:click="stop" class="bg-brick-500 hover:bg-brick-500/90 text-white text-xs font-disp font-bold px-3 py-1.5 rounded-lg">STOP</button>
            </div>
        </div>
    @endif
</div>
