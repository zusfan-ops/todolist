<div class="px-5 pt-5 space-y-5">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">Analitik</h2>
        <div class="flex gap-1 bg-ink-100 dark:bg-ink-700 rounded-xl p-0.5">
            <button wire:click="setPeriod('weekly')"
                    class="text-xs font-disp font-bold px-3 py-1.5 rounded-lg transition-colors
                           {{ $period === 'weekly' ? 'bg-white dark:bg-ink-500 text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-ink-300' }}">
                Mingguan
            </button>
            <button wire:click="setPeriod('monthly')"
                    class="text-xs font-disp font-bold px-3 py-1.5 rounded-lg transition-colors
                           {{ $period === 'monthly' ? 'bg-white dark:bg-ink-500 text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-ink-300' }}">
                Bulanan
            </button>
        </div>
    </div>

    @if ($period === 'weekly')
        <div class="bg-white dark:bg-ink-700 rounded-2xl p-5 border border-ink-100 dark:border-ink-500">
            <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-300 mb-3 font-disp font-bold">5 Minggu Terakhir</p>
            <div class="flex items-end gap-2 h-32">
                @php $maxMinutes = max(1, ...collect($weeks)->pluck('minutes')->toArray()); @endphp
                @foreach ($weeks as $week)
                    <div class="flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                        <span class="text-[9px] font-mono text-ink-500 dark:text-ink-300">{{ intdiv($week['minutes'], 60) }}j</span>
                        <div class="w-full rounded-t {{ $week['minutes'] > 0 ? 'bg-vest-500' : 'bg-ink-100 dark:bg-ink-500' }}" style="height: {{ max(4, round($week['minutes'] / $maxMinutes * 100)) }}px"></div>
                        <span class="text-[9px] text-ink-500 dark:text-ink-300 font-mono text-center leading-tight">{{ $week['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white dark:bg-ink-700 rounded-xl p-4 border border-ink-100 dark:border-ink-500">
                <p class="font-mono font-bold text-2xl text-ink-900 dark:text-white">{{ intdiv($weeklyTotal, 60) }}j {{ $weeklyTotal % 60 }}m</p>
                <p class="text-xs text-ink-500 dark:text-ink-300">Total jam &mdash; Minggu ini</p>
            </div>
            <div class="bg-white dark:bg-ink-700 rounded-xl p-4 border border-ink-100 dark:border-ink-500">
                <p class="font-mono font-bold text-2xl text-leaf-500">{{ $weeks->last()['completed'] ?? 0 }}</p>
                <p class="text-xs text-ink-500 dark:text-ink-300">Task selesai &mdash; Minggu ini</p>
            </div>
        </div>

        <div class="bg-white dark:bg-ink-700 rounded-2xl p-5 border border-ink-100 dark:border-ink-500">
            <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-300 mb-3 font-disp font-bold">Task Selesai per Minggu</p>
            <div class="flex items-end gap-2 h-24">
                @php $maxCompleted = max(1, ...collect($weeks)->pluck('completed')->toArray()); @endphp
                @foreach ($weeks as $week)
                    <div class="flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                        <span class="text-[9px] font-mono text-ink-500 dark:text-ink-300">{{ $week['completed'] }}</span>
                        <div class="w-full rounded-t {{ $week['completed'] > 0 ? 'bg-leaf-500' : 'bg-ink-100 dark:bg-ink-500' }}" style="height: {{ max(4, round($week['completed'] / $maxCompleted * 72)) }}px"></div>
                        <span class="text-[9px] text-ink-500 dark:text-ink-300 font-mono text-center leading-tight">{{ $week['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    @else
        <div class="bg-white dark:bg-ink-700 rounded-2xl p-5 border border-ink-100 dark:border-ink-500">
            <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-300 mb-3 font-disp font-bold">6 Bulan Terakhir</p>
            <div class="flex items-end gap-2 h-32">
                @php $maxMinutes = max(1, ...collect($monthlyMinutes)->pluck('minutes')->toArray()); @endphp
                @foreach ($monthlyMinutes as $month)
                    <div class="flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                        <span class="text-[9px] font-mono text-ink-500 dark:text-ink-300">{{ intdiv($month['minutes'], 60) }}j</span>
                        <div class="w-full rounded-t {{ $month['minutes'] > 0 ? 'bg-vest-500' : 'bg-ink-100 dark:bg-ink-500' }}" style="height: {{ max(4, round($month['minutes'] / $maxMinutes * 100)) }}px"></div>
                        <span class="text-[9px] text-ink-500 dark:text-ink-300 font-mono text-center leading-tight">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($projectBreakdown && $projectBreakdown->count())
            <div class="bg-white dark:bg-ink-700 rounded-2xl p-5 border border-ink-100 dark:border-ink-500">
                <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-300 mb-3 font-disp font-bold">Task Selesai per Proyek (Bulan Ini)</p>
                <div class="space-y-2.5">
                    @foreach ($projectBreakdown as $item)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-ink-900 dark:text-white">{{ $item['name'] }}</span>
                            <span class="font-mono font-bold text-leaf-500">{{ $item['completed'] }} task</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-ink-700 rounded-xl p-4 border border-ink-100 dark:border-ink-500">
            <p class="font-mono font-bold text-2xl text-leaf-500">{{ $monthlyMinutes->sum('completed') }}</p>
            <p class="text-xs text-ink-500 dark:text-ink-300">Total task selesai &mdash; 6 bulan</p>
        </div>
    @endif
</div>