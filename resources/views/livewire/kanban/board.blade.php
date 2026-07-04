<div class="pt-4"
     x-data="{
        confirmMove: null,
        init() {
            this.$wire.on('confirm-move', (e) => { this.confirmMove = e; this.askConfirm(); });
        },
        askConfirm() {
            if (! this.confirmMove) return;
            const e = this.confirmMove;
            this.confirmMove = null;
            if (confirm(e.message)) {
                this.$wire.moveTask(e.taskId, e.toColumnId, e.position, true);
            }
        },
        onDrop(evt, columnEl) {
            const taskId = +evt.item.dataset.id;
            const toColumnId = +columnEl.dataset.colId;
            const isDoneColumn = columnEl.dataset.doneColumn === '1';
            const progress = +evt.item.dataset.progress;

            if (isDoneColumn && progress < 100 && ! confirm(`Checklist belum selesai (${progress}%). Tetap tandai selesai?`)) {
                this.$wire.$refresh();
                return;
            }

            const siblings = [...evt.to.children].map(el => ({ id: +el.dataset.id, pos: +el.dataset.position }));
            const idx = siblings.findIndex(s => s.id === taskId);
            const prevPos = siblings[idx - 1]?.pos ?? 0;
            const nextPos = siblings[idx + 1]?.pos ?? (prevPos + 2000);
            const newPosition = Math.floor((prevPos + nextPos) / 2);

            this.$wire.moveTask(taskId, toColumnId, newPosition, isDoneColumn && progress < 100);
        }
     }">

    <div class="px-5 flex gap-2 mb-3 overflow-x-auto no-scrollbar">
        @foreach ($projects as $project)
            <button wire:click="selectProject({{ $project->id }})"
                    class="text-xs font-disp font-bold px-3 py-1.5 rounded-full whitespace-nowrap border {{ $activeProjectId === $project->id ? 'bg-ink-900 text-white border-ink-900' : 'bg-white text-ink-700 border-ink-100' }}">
                {{ $project->name }}
            </button>
        @endforeach
        @if (auth()->user()->isOwner())
            <button wire:click="openNewProjectModal"
                    class="text-xs font-disp font-bold px-3 py-1.5 rounded-full whitespace-nowrap border-2 border-dashed border-ink-300 text-ink-500 shrink-0">
                + Proyek
            </button>
        @endif
    </div>

    @if ($projects->isEmpty())
        <div class="px-5">
            <p class="text-xs text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">
                @if (auth()->user()->isOwner())
                    Belum ada proyek. Buat proyek pertamamu dengan tombol "+ Proyek" di atas.
                @else
                    Belum ada proyek yang ditugaskan untukmu. Hubungi pemilik akun.
                @endif
            </p>
        </div>
    @endif

    <div class="kanban-scroll flex gap-3 overflow-x-auto px-5 pb-2 no-scrollbar">
        @foreach ($columns as $column)
            @php $overWip = $column->wip_limit && $column->tasks->count() > $column->wip_limit; @endphp
            <div class="w-[78%] shrink-0" wire:key="col-wrap-{{ $column->id }}">
                <div class="flex items-center justify-between mb-2 px-1">
                    <h3 class="font-disp font-bold text-xs uppercase tracking-wider {{ $column->is_done_column ? 'text-leaf-500' : 'text-ink-700' }}">{{ $column->name }}</h3>
                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded {{ $overWip ? 'bg-vest-100 text-vest-600 font-bold' : 'bg-ink-100 text-ink-500' }}">
                        {{ $column->tasks->count() }}{{ $column->wip_limit ? '/'.$column->wip_limit : '' }}
                    </span>
                </div>
                <div wire:key="col-{{ $column->id }}"
                     data-col-id="{{ $column->id }}"
                     data-done-column="{{ $column->is_done_column ? '1' : '0' }}"
                     x-init="Sortable.create($el, { group: 'kanban', animation: 180, delay: 120, delayOnTouchOnly: true, onEnd: (evt) => onDrop(evt, $el) })"
                     class="space-y-2.5 min-h-[120px] rounded-xl p-1">
                    @foreach ($column->tasks as $task)
                        <x-task-card :task="$task" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <p class="text-center text-[10px] text-ink-500 mt-1">Geser kartu antar kolom &middot; geser layar untuk kolom lain</p>

    @if ($showNewProjectModal)
        <div wire:click.self="$set('showNewProjectModal', false)" class="absolute inset-0 bg-ink-900/50 flex items-end z-20">
            <div class="bg-white w-full rounded-t-3xl p-5">
                <h3 class="font-disp font-bold text-ink-900 mb-3">Proyek baru</h3>
                <input wire:model="newProjectName" wire:keydown.enter="createProject" placeholder="Nama proyek (mis. Cak Goto Cabang 2)" autofocus
                       class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
                @error('newProjectName') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror

                <div class="flex gap-2 mt-3">
                    @foreach (\App\Livewire\Kanban\Board::COLOR_PALETTE as $color)
                        <button type="button" wire:click="$set('newProjectColor', '{{ $color }}')"
                                class="w-9 h-9 rounded-full border-2 {{ $newProjectColor === $color ? 'border-ink-900' : 'border-transparent' }}"
                                style="background: {{ $color }}"></button>
                    @endforeach
                </div>

                <button wire:click="createProject" class="w-full bg-ink-900 text-white font-disp font-bold py-3.5 rounded-xl mt-4">
                    <span wire:loading.remove wire:target="createProject">Buat Proyek</span>
                    <span wire:loading wire:target="createProject">Menyimpan…</span>
                </button>
            </div>
        </div>
    @endif
</div>
