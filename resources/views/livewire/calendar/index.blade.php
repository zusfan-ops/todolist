<div class="px-5 pt-5 space-y-4" wire:poll.60s>
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">Kalender</h2>
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="w-8 h-8 rounded-full bg-ink-100 dark:bg-ink-700 text-ink-700 dark:text-ink-300 grid place-items-center text-sm">&larr;</button>
            <span class="font-disp font-bold text-sm text-ink-900 dark:text-white min-w-[140px] text-center">{{ $monthName }}</span>
            <button wire:click="nextMonth" class="w-8 h-8 rounded-full bg-ink-100 dark:bg-ink-700 text-ink-700 dark:text-ink-300 grid place-items-center text-sm">&rarr;</button>
        </div>
    </div>

    <div class="bg-white dark:bg-ink-700 rounded-2xl p-4 border border-ink-100 dark:border-ink-500">
        <div class="grid grid-cols-7 mb-2">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <span class="text-center text-[10px] font-disp font-bold text-ink-500 dark:text-ink-300 uppercase">{{ $dayName }}</span>
            @endforeach
        </div>
        @foreach ($weeks as $week)
            <div class="grid grid-cols-7">
                @foreach ($week as $cell)
                    @if ($cell)
                        @php
                            $hasTasks = $cell['tasks']->count() > 0;
                            $hasNotes = $cell['notes']->count() > 0;
                            $isSelected = $cell['date'] === $selectedDate;
                        @endphp
                        <button wire:click="selectDate('{{ $cell['date'] }}')"
                                class="relative flex flex-col items-center justify-center py-1.5 rounded-lg transition-colors
                                       {{ $isSelected ? 'bg-ink-900 dark:bg-white text-white' : ($cell['isToday'] ? 'bg-vest-100 dark:bg-vest-500/20' : 'hover:bg-ink-50 dark:hover:bg-ink-500') }}">
                            <span class="text-xs font-disp font-bold {{ $isSelected ? 'text-white' : ($cell['isToday'] ? 'text-ink-900 dark:text-vest-500' : 'text-ink-900 dark:text-white') }}">
                                {{ $cell['day'] }}
                            </span>
                            @if ($hasTasks || $hasNotes)
                                <span class="flex items-center gap-0.5 mt-0.5">
                                    @if ($hasTasks)
                                        <span class="w-1 h-1 rounded-full {{ $isSelected ? 'bg-white' : 'bg-vest-500' }}"></span>
                                    @endif
                                    @if ($hasNotes)
                                        <span class="w-1 h-1 rounded-full {{ $isSelected ? 'bg-white' : 'bg-leaf-500' }}"></span>
                                    @endif
                                </span>
                            @endif
                        </button>
                    @else
                        <div class="py-1.5"></div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    @if ($selectedDate)
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-disp font-bold text-sm text-ink-900 dark:text-white uppercase tracking-wider">
                    {{ now()->parse($selectedDate)->translatedFormat('l, j F') }}
                </h3>
                <a href="{{ route('notes', ['date' => $selectedDate]) }}" wire:navigate class="text-[10px] font-disp font-bold text-vest-500">+ Catatan</a>
            </div>
            <div class="space-y-2.5">
                @forelse ($selectedDateTasks as $task)
                    <x-task-card :task="$task" wire:key="cal-{{ $task->id }}" />
                @empty
                    <p class="text-sm text-ink-500 dark:text-ink-300 bg-white dark:bg-ink-700 border border-dashed border-ink-300 dark:border-ink-500 rounded-xl p-5 text-center">
                        Tidak ada task dengan tenggat di tanggal ini.
                    </p>
                @endforelse
            </div>

            @if ($selectedDateNotes->count())
                <div class="mt-4 space-y-1.5">
                    <h4 class="font-disp font-bold text-xs text-leaf-600 dark:text-leaf-400 uppercase tracking-wider mb-2">Catatan</h4>
                    @foreach ($selectedDateNotes as $note)
                        <a href="{{ route('notes') }}" wire:navigate
                           class="block bg-white dark:bg-ink-700 border border-ink-100 dark:border-ink-500 rounded-xl p-3 hover:border-ink-300 transition-colors">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-disp font-bold px-2 py-0.5 rounded-full shrink-0
                                    {{ $note->category === 'umum' ? 'bg-ink-100 dark:bg-ink-500 text-ink-700 dark:text-ink-200' : '' }}
                                    {{ $note->category === 'ide' ? 'bg-vest-100 dark:bg-vest-500/20 text-vest-600' : '' }}
                                    {{ $note->category === 'meeting' ? 'bg-leaf-100 dark:bg-leaf-500/20 text-leaf-600' : '' }}
                                    {{ $note->category === 'tugas' ? 'bg-brick-100 dark:bg-brick-500/20 text-brick-600' : '' }}
                                    {{ $note->category === 'pribadi' ? 'bg-ink-900/10 dark:bg-white/10 text-ink-700 dark:text-ink-200' : '' }}">
                                    {{ ucfirst($note->category) }}
                                </span>
                                <span class="font-disp font-bold text-sm text-ink-900 dark:text-white truncate">{{ $note->title }}</span>
                            </div>
                            @if ($note->attachments->count())
                                <div class="flex items-center gap-1 mt-1 text-[10px] text-ink-500 dark:text-ink-300">
                                    <span>📎 {{ $note->attachments->count() }}</span>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>