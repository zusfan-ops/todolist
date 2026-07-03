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
     class="bg-white rounded-xl border border-ink-100 p-3 cursor-pointer active:scale-[.98] transition-transform">
    <div class="flex items-center justify-between mb-1.5">
        <span class="text-[9px] font-disp font-bold uppercase" style="color: {{ $task->project->color }}">{{ $task->project->name }}</span>
        @if ($task->priority === 'urgent')
            <span class="hazard text-[8px] font-disp font-bold text-white px-1.5 py-0.5 rounded">MENDESAK</span>
        @elseif ($task->priority === 'high')
            <span class="text-[8px] font-disp font-bold text-brick-500 bg-brick-100 px-1.5 py-0.5 rounded">TINGGI</span>
        @endif
    </div>
    <p class="text-sm font-medium text-ink-900 leading-snug">{{ $task->title }}</p>
    <div class="prog-track mt-2.5">
        <div class="prog-fill {{ $done ? 'done' : '' }}" style="width: {{ $progress }}%"></div>
    </div>
    <div class="flex items-center justify-between mt-1.5">
        <span class="font-mono text-[10px] font-bold {{ $done ? 'text-leaf-500' : 'text-ink-700' }}">{{ $progress }}%</span>
        <div class="flex items-center gap-2">
            @if ($checklistTotal)
                <span class="font-mono text-[10px] text-ink-500">☑ {{ $checklistDone }}/{{ $checklistTotal }}</span>
            @endif
            @if ($task->has_active_timer ?? false)
                <span class="font-mono text-[10px] text-vest-600 font-bold animate-pulse">⏱ berjalan</span>
            @endif
            @if ($isOverdue)
                <span class="font-mono text-[10px] text-brick-500 font-bold">● terlambat</span>
            @elseif ($isDueToday)
                <span class="font-mono text-[10px] text-brick-500 font-bold">● hari ini</span>
            @endif
        </div>
    </div>
</div>
