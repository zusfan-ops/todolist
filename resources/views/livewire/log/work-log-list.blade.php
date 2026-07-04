@php
    $dayLabels = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
    $maxMinutes = max(1, ...$weeklyMinutes);
@endphp

<div class="px-5 pt-6 space-y-5">
    <h2 class="font-disp font-bold text-ink-900 text-base uppercase tracking-wider">Rekam Pekerjaan</h2>

    <div class="bg-ink-900 rounded-2xl p-5 text-white">
        <p class="text-xs uppercase tracking-wider text-ink-300 mb-3">Minggu ini</p>
        <div class="flex items-end gap-2 h-24">
            @foreach ($weeklyMinutes as $i => $minutes)
                <div class="flex-1 flex flex-col items-center justify-end gap-1 h-full">
                    <div class="w-full rounded-t {{ $minutes === 0 ? 'bg-ink-700' : 'bg-vest-500' }}" style="height: {{ max(4, round($minutes / $maxMinutes * 80)) }}px"></div>
                    <span class="text-[11px] text-ink-300 font-mono">{{ $dayLabels[$i] }}</span>
                </div>
            @endforeach
        </div>
        <p class="font-mono text-base mt-3">
            <span class="font-bold text-vest-500">{{ intdiv($weeklyTotal, 60) }}j {{ $weeklyTotal % 60 }}m</span>
            <span class="text-ink-300">total minggu ini</span>
        </p>
    </div>

    <div class="space-y-2.5">
        @forelse ($logs as $log)
            <div class="bg-white rounded-xl border border-ink-100 p-4 flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-base font-medium text-ink-900 truncate">{{ $log->task->title }}</p>
                    <p class="text-xs text-ink-500 mt-0.5">
                        {{ $log->started_at->copy()->setTimezone(auth()->user()->displayTimezone())->translatedFormat('j M, H:i') }}
                        @if ($log->note) &middot; {{ $log->note }} @endif
                    </p>
                </div>
                <div class="text-right shrink-0 ml-3">
                    <p class="font-mono font-bold text-ink-900">{{ intdiv($log->duration_minutes ?? 0, 60) }}j {{ ($log->duration_minutes ?? 0) % 60 }}m</p>
                    <p class="text-[11px] uppercase text-ink-300 font-mono">{{ $log->source }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">Belum ada log pekerjaan.</p>
        @endforelse
    </div>

    @if ($showManualForm)
        <form wire:submit="saveManual" class="bg-white rounded-xl border border-ink-100 p-5 space-y-4">
            <h3 class="font-disp font-bold text-base text-ink-900">Catat manual</h3>
            <select wire:model="taskId" class="w-full bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base">
                <option value="">Pilih task…</option>
                @foreach ($tasks as $task)
                    <option value="{{ $task->id }}">{{ $task->project->name }} &middot; {{ $task->title }}</option>
                @endforeach
            </select>
            @error('taskId') <p class="text-brick-500 text-sm">{{ $message }}</p> @enderror

            <input type="date" wire:model="date" class="w-full bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base">
            @error('date') <p class="text-brick-500 text-sm">{{ $message }}</p> @enderror

            <div class="flex gap-3">
                <input type="number" wire:model="durationHours" placeholder="Jam" min="0" max="16" class="w-1/2 bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base">
                <input type="number" wire:model="durationMinutes" placeholder="Menit" min="0" max="59" class="w-1/2 bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base">
            </div>
            @error('durationMinutes') <p class="text-brick-500 text-sm">{{ $message }}</p> @enderror

            <textarea wire:model="note" placeholder="Catatan (opsional)" rows="2" class="w-full bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base"></textarea>

            <div class="flex gap-3">
                <button type="button" wire:click="$set('showManualForm', false)" class="flex-1 text-sm font-disp font-bold text-ink-700 border border-ink-100 rounded-xl py-3.5">Batal</button>
                <button type="submit" class="flex-1 bg-ink-900 text-white text-sm font-disp font-bold rounded-xl py-3.5">Simpan</button>
            </div>
        </form>
    @else
        <button wire:click="openManualForm" class="w-full text-sm font-disp font-bold text-ink-700 border-2 border-dashed border-ink-300 rounded-xl py-3.5">+ Catat manual</button>
    @endif
</div>
