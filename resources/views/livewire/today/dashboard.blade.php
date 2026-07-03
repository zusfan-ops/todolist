<div class="px-5 pt-5 space-y-5" wire:poll.60s>
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-white rounded-xl p-3 border border-ink-100">
            <p class="font-mono font-bold text-xl text-ink-900">{{ intdiv($minutesToday, 60) }}j {{ $minutesToday % 60 }}m</p>
            <p class="text-[10px] text-ink-500 uppercase tracking-wide">Jam kerja</p>
        </div>
        <div class="bg-white rounded-xl p-3 border border-ink-100">
            <p class="font-mono font-bold text-xl text-leaf-500">{{ $completedToday }}</p>
            <p class="text-[10px] text-ink-500 uppercase tracking-wide">Selesai</p>
        </div>
        <div class="bg-white rounded-xl p-3 border border-ink-100">
            <p class="font-mono font-bold text-xl text-brick-500">{{ $dueToday->count() }}</p>
            <p class="text-[10px] text-ink-500 uppercase tracking-wide">Due hari ini</p>
        </div>
    </div>

    <div>
        <h2 class="font-disp font-bold text-ink-900 text-sm uppercase tracking-wider mb-2">Jatuh tempo hari ini</h2>
        <div class="space-y-2.5">
            @forelse ($dueToday as $task)
                <x-task-card :task="$task" wire:key="due-{{ $task->id }}" />
            @empty
                <p class="text-xs text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-4 text-center">Tidak ada tenggat hari ini. Tambahkan task dengan tombol +</p>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="font-disp font-bold text-ink-900 text-sm uppercase tracking-wider mb-2">Sedang dikerjakan</h2>
        <div class="space-y-2.5">
            @forelse ($inProgress as $task)
                <x-task-card :task="$task" wire:key="doing-{{ $task->id }}" />
            @empty
                <p class="text-xs text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-4 text-center">Belum ada task yang sedang dikerjakan.</p>
            @endforelse
        </div>
    </div>
</div>
