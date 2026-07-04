<div class="pt-4">
    <div class="px-5 flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">Proyek</h2>
            <div class="flex bg-ink-100 dark:bg-ink-600 rounded-lg p-0.5">
                <span class="text-[10px] font-disp font-bold px-2.5 py-1 rounded-md bg-white dark:bg-ink-800 text-ink-900 dark:text-white shadow-sm">List</span>
                <a href="{{ route('kanban') }}" wire:navigate class="text-[10px] font-disp font-bold px-2.5 py-1 rounded-md text-ink-500 dark:text-ink-300 hover:text-ink-900 dark:hover:text-white">Board</a>
            </div>
        </div>
    </div>

    <div class="px-5 flex gap-2 mb-4 overflow-x-auto no-scrollbar">
        @foreach ($projects as $project)
            @php $isActive = $activeProjectId === $project->id; @endphp
            <div class="relative shrink-0">
                <button wire:click="selectProject({{ $project->id }})"
                        class="text-sm font-disp font-bold px-4 py-2 rounded-full whitespace-nowrap border {{ $isActive ? 'bg-ink-900 text-white border-ink-900' : 'bg-white dark:bg-ink-700 text-ink-700 dark:text-ink-100 border-ink-100 dark:border-ink-500' }}">
                    {{ $project->name }}
                </button>
                @if ($isActive && auth()->user()->canManageProject($project))
                    <button wire:click="deleteProject({{ $project->id }})"
                            wire:confirm="Hapus proyek &quot;{{ $project->name }}&quot;? Semua tugas di dalamnya akan ikut terhapus."
                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-vest-500 text-white rounded-full grid place-items-center shadow"
                            title="Hapus proyek">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                @endif
            </div>
        @endforeach
        @if (auth()->user()->isOwner())
            <button wire:click="openNewProjectModal"
                    class="text-sm font-disp font-bold px-4 py-2 rounded-full whitespace-nowrap border-2 border-dashed border-ink-300 dark:border-ink-500 text-ink-500 dark:text-ink-300 shrink-0">
                + Proyek
            </button>
        @endif
    </div>

    @if ($projects->isEmpty())
        <div class="px-5 mb-4">
            <p class="text-sm text-ink-500 dark:text-ink-300 bg-white dark:bg-ink-700 border border-dashed border-ink-300 dark:border-ink-500 rounded-xl p-6 text-center">
                @if (auth()->user()->isOwner())
                    Belum ada proyek. Buat proyek pertamamu dengan tombol "+ Proyek" di atas.
                @else
                    Belum ada proyek yang ditugaskan untukmu. Hubungi pemilik akun.
                @endif
            </p>
        </div>
    @endif

    @if ($activeProjectId)
        <div class="px-5 mb-4">
            <a href="{{ route('kanban') }}" wire:navigate
               class="inline-flex items-center gap-1.5 text-xs font-disp font-bold text-vest-600 dark:text-vest-400 bg-vest-50 dark:bg-vest-500/10 px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>
                Lihat di Board
            </a>
        </div>
    @endif

    <div class="px-5 space-y-5 pb-2">
        <div class="flex items-center justify-between">
            <h3 class="font-disp font-bold text-sm text-ink-500 dark:text-ink-300 uppercase tracking-wider">Daftar Cepat</h3>
            @if ($doneCount > 0)
                <button wire:click="clearCompleted" class="text-xs text-ink-500 dark:text-ink-300 font-disp font-bold">Hapus yang selesai</button>
            @endif
        </div>

        <form wire:submit="add" class="flex gap-2">
            <input wire:model="newBody" placeholder="Tulis yang mau dikerjakan…" autofocus
                   class="flex-1 bg-white dark:bg-ink-700 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3.5 text-base focus:outline-none focus:border-vest-500">
            <button type="submit" class="bg-vest-500 text-ink-900 font-disp font-bold px-5 rounded-xl text-lg">+</button>
        </form>
        @error('newBody') <p class="text-brick-500 text-sm -mt-2">{{ $message }}</p> @enderror

        <div class="space-y-2.5">
            @forelse ($todos as $todo)
                <div wire:key="todo-{{ $todo->id }}" class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 px-4 py-3.5 flex items-center gap-3">
                    <button wire:click="toggle({{ $todo->id }})"
                            class="w-7 h-7 rounded-full border-2 shrink-0 grid place-items-center {{ $todo->is_done ? 'bg-leaf-500 border-leaf-500' : 'border-ink-300 dark:border-ink-500' }}">
                        @if ($todo->is_done)
                            <span class="text-white text-sm leading-none">✓</span>
                        @endif
                    </button>
                    <span class="flex-1 text-base {{ $todo->is_done ? 'line-through text-ink-300 dark:text-ink-400' : 'text-ink-900 dark:text-white' }}">{{ $todo->body }}</span>
                    <button wire:click="delete({{ $todo->id }})" class="text-ink-300 dark:text-ink-400 text-xl leading-none shrink-0">&times;</button>
                </div>
            @empty
                <p class="text-sm text-ink-500 dark:text-ink-300 bg-white dark:bg-ink-700 border border-dashed border-ink-300 dark:border-ink-500 rounded-xl p-6 text-center">
                    Belum ada yang ditulis. Ketik di atas lalu tekan Enter.
                </p>
            @endforelse
        </div>
    </div>

    {{-- New Project Modal --}}
    @if ($showNewProjectModal)
        <div wire:click.self="$set('showNewProjectModal', false)" class="fixed inset-0 bg-ink-900/50 flex items-end z-30">
            <div class="bg-white dark:bg-ink-700 w-full rounded-t-3xl p-6">
                <h3 class="font-disp font-bold text-lg text-ink-900 dark:text-white mb-4">Proyek baru</h3>
                <input wire:model="newProjectName" wire:keydown.enter="createProject" placeholder="Nama proyek (mis. Cak Goto Cabang 2)" autofocus
                       class="w-full bg-ink-50 dark:bg-ink-800 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3.5 text-base focus:outline-none focus:border-vest-500">
                @error('newProjectName') <p class="text-brick-500 text-sm mt-1">{{ $message }}</p> @enderror

                <div class="flex gap-3 mt-4">
                    @foreach (\App\Livewire\Todo\Index::COLOR_PALETTE as $color)
                        <button type="button" wire:click="$set('newProjectColor', '{{ $color }}')"
                                class="w-10 h-10 rounded-full border-2 {{ $newProjectColor === $color ? 'border-ink-900' : 'border-transparent' }}"
                                style="background: {{ $color }}"></button>
                    @endforeach
                </div>

                <button wire:click="createProject" class="w-full bg-ink-900 text-white font-disp font-bold py-4 rounded-xl mt-5 text-base">
                    <span wire:loading.remove wire:target="createProject">Buat Proyek</span>
                    <span wire:loading wire:target="createProject">Menyimpan…</span>
                </button>
            </div>
        </div>
    @endif
</div>
