@props(['task'])

@php
    $progress = $task->progress_cached;
    $done = $progress === 100;
    $checklistTotal = $task->checklistItems->count();
    $checklistDone = $task->checklistItems->where('is_done', true)->count();
    $isOverdue = $task->due_date && $task->due_date->isPast() && ! $task->due_date->isToday() && ! $done;
    $isDueToday = $task->due_date && $task->due_date->isToday();
@endphp

<div wire:key="task-{{ $task->id }}" data-id="{{ $task->id }}" data-position="{{ $task->position }}" data-progress="{{ $progress }}"
     wire:click="$dispatch('open-task', { taskId: {{ $task->id }} })"
     class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 p-4 cursor-pointer active:scale-[.98] transition-transform">
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-disp font-bold uppercase" style="color: {{ $task->project->color }}">{{ $task->project->name }}</span>
        @if ($task->priority === 'urgent')
            <span class="hazard text-[10px] font-disp font-bold text-white px-2 py-0.5 rounded">MENDESAK</span>
        @elseif ($task->priority === 'high')
            <span class="text-[10px] font-disp font-bold text-brick-500 bg-brick-100 dark:bg-brick-500/20 px-2 py-0.5 rounded">TINGGI</span>
        @endif
    </div>
    <p class="text-base font-medium text-ink-900 dark:text-white leading-snug">{{ $task->title }}</p>
    <div class="prog-track mt-3">
        <div class="prog-fill {{ $done ? 'done' : '' }}" style="width: {{ $progress }}%"></div>
    </div>
    <div class="flex items-center justify-between mt-2">
        <span class="font-mono text-xs font-bold {{ $done ? 'text-leaf-500' : 'text-ink-700 dark:text-ink-100' }}">{{ $progress }}%</span>
        <div class="flex items-center gap-3">
            @if ($checklistTotal)
                <span class="font-mono text-xs text-ink-500 dark:text-ink-300">☑ {{ $checklistDone }}/{{ $checklistTotal }}</span>
            @endif
            @if ($task->has_active_timer ?? false)
                <span class="font-mono text-xs text-vest-600 font-bold animate-pulse">⏱ berjalan</span>
            @endif
            @if ($isOverdue)
                <span class="font-mono text-xs text-brick-500 font-bold">● terlambat</span>
            @elseif ($isDueToday)
                <span class="font-mono text-xs text-brick-500 font-bold">● hari ini</span>
            @endif
        </div>
    </div>
</div>
