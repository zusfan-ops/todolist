<div class="px-5 pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">To Do</h2>
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
