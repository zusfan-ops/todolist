<div x-data="{
        photoType: 'progress',
        capturing: false,
        init() {
            this.$wire.on('confirm-start-timer', (e) => {
                if (confirm(e.message)) this.$wire.startTimer(true);
            });
        },
        async onFile(e, taskId) {
            const file = e.target.files[0];
            e.target.value = '';
            if (!file) return;
            this.capturing = true;
            try {
                await window.KerjaKuPhoto.capturePhoto(file, { taskId, type: this.photoType });
            } finally {
                this.capturing = false;
            }
        }
     }">
    @if ($task)
        <div wire:click.self="close" class="absolute inset-0 bg-ink-900/50 flex items-end z-20">
            <div class="bg-white w-full rounded-t-3xl max-h-[85%] overflow-y-auto no-scrollbar p-5">
                <div class="w-10 h-1 bg-ink-100 rounded-full mx-auto mb-4"></div>

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-disp font-bold uppercase px-2 py-0.5 rounded"
                              style="background: {{ $task->project->color }}22; color: {{ $task->project->color }}">
                            {{ $task->project->name }}
                        </span>
                        <h3 class="font-disp font-bold text-lg text-ink-900 mt-1.5">{{ $task->title }}</h3>
                    </div>
                    @if ($task->priority === 'urgent')
                        <span class="hazard text-[9px] font-disp font-bold text-white px-2 py-1 rounded shrink-0">MENDESAK</span>
                    @endif
                </div>

                @if ($task->description)
                    <p class="text-sm text-ink-500 mt-2">{{ $task->description }}</p>
                @endif

                <div class="mt-4">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-ink-500">Progress</span>
                        <span class="font-mono font-bold {{ $task->progress_cached === 100 ? 'text-leaf-500' : 'text-ink-900' }}">{{ $task->progress_cached }}%</span>
                    </div>
                    <div class="prog-track h-2">
                        <div class="prog-fill {{ $task->progress_cached === 100 ? 'done' : '' }}" style="width: {{ $task->progress_cached }}%"></div>
                    </div>
                </div>

                <div class="mt-5">
                    <h4 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-700 mb-2">Checklist</h4>
                    <div class="space-y-1.5">
                        @foreach ($task->checklistItems as $item)
                            <label wire:key="check-{{ $item->id }}" class="flex items-center gap-2.5 bg-ink-50 rounded-lg px-3 py-2.5 cursor-pointer">
                                <input type="checkbox" wire:click="toggleChecklist({{ $item->id }})" @checked($item->is_done) class="w-4 h-4 accent-leaf-500">
                                <span class="text-sm {{ $item->is_done ? 'line-through text-ink-300' : 'text-ink-900' }}">{{ $item->body }}</span>
                            </label>
                        @endforeach
                    </div>
                    <form wire:submit="addChecklistItem" class="flex gap-2 mt-2">
                        <input wire:model="newChecklistBody" placeholder="Tambah item…" class="flex-1 bg-ink-50 border border-ink-100 rounded-lg px-3 py-2 text-sm">
                        <button type="submit" class="bg-ink-100 text-ink-700 text-xs font-disp font-bold px-3 rounded-lg">+</button>
                    </form>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-5">
                    <button wire:click="startTimer" class="bg-ink-900 text-white font-disp font-bold text-sm py-3 rounded-xl">⏱ Mulai timer</button>
                    <label class="bg-vest-500 text-ink-900 font-disp font-bold text-sm py-3 rounded-xl text-center cursor-pointer" :class="capturing && 'opacity-50 pointer-events-none'">
                        <span x-text="capturing ? 'Memproses…' : '📷 Foto'"></span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="onFile($event, {{ $task->id }})">
                    </label>
                </div>

                <div class="flex gap-1.5 mt-3">
                    @foreach (['before' => 'SEBELUM', 'progress' => 'PROSES', 'after' => 'SESUDAH', 'proof' => 'BUKTI'] as $val => $label)
                        <button type="button" @click="photoType = '{{ $val }}'"
                                class="flex-1 text-[9px] font-disp font-bold py-1.5 rounded"
                                :class="photoType === '{{ $val }}' ? 'bg-ink-900 text-white' : 'bg-ink-50 text-ink-500'">{{ $label }}</button>
                    @endforeach
                </div>

                @if ($task->photos->count())
                    <div class="grid grid-cols-4 gap-1.5 mt-3">
                        @foreach ($task->photos as $ph)
                            <img src="{{ $ph->thumb_url }}" class="aspect-square object-cover rounded-lg">
                        @endforeach
                    </div>
                @endif

                <div class="mt-5 mb-2">
                    <h4 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-700 mb-2">Aktivitas</h4>
                    <div class="border-l-2 border-ink-100 pl-3 space-y-2.5">
                        @foreach ($task->activities as $activity)
                            <p class="text-xs text-ink-500">
                                <span class="font-mono text-ink-300">{{ $activity->created_at->copy()->setTimezone(auth()->user()->displayTimezone())->diffForHumans() }}</span>
                                &middot; {{ str_replace('_', ' ', $activity->event) }}
                            </p>
                        @endforeach
                    </div>
                </div>

                <button @click="if (confirm('Hapus task ini? Checklist, foto, dan log jam kerjanya tetap tersimpan tapi task tidak akan muncul lagi.')) $wire.deleteTask()"
                        class="w-full text-xs font-disp font-bold text-brick-500 border border-brick-500/30 rounded-xl py-3 mt-2">
                    Hapus Task
                </button>
            </div>
        </div>
    @endif
</div>
