<div class="px-5 pt-5 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 text-sm uppercase tracking-wider">To Do</h2>
        @if ($doneCount > 0)
            <button wire:click="clearCompleted" class="text-[11px] text-ink-500 font-disp font-bold">Hapus yang selesai</button>
        @endif
    </div>

    <form wire:submit="add" class="flex gap-2">
        <input wire:model="newBody" placeholder="Tulis yang mau dikerjakan…" autofocus
               class="flex-1 bg-white border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
        <button type="submit" class="bg-vest-500 text-ink-900 font-disp font-bold px-4 rounded-xl">+</button>
    </form>
    @error('newBody') <p class="text-brick-500 text-xs -mt-2">{{ $message }}</p> @enderror

    <div class="space-y-2">
        @forelse ($todos as $todo)
            <div wire:key="todo-{{ $todo->id }}" class="bg-white rounded-xl border border-ink-100 px-4 py-3 flex items-center gap-3">
                <button wire:click="toggle({{ $todo->id }})"
                        class="w-6 h-6 rounded-full border-2 shrink-0 grid place-items-center {{ $todo->is_done ? 'bg-leaf-500 border-leaf-500' : 'border-ink-300' }}">
                    @if ($todo->is_done)
                        <span class="text-white text-xs leading-none">✓</span>
                    @endif
                </button>
                <span class="flex-1 text-sm {{ $todo->is_done ? 'line-through text-ink-300' : 'text-ink-900' }}">{{ $todo->body }}</span>
                <button wire:click="delete({{ $todo->id }})" class="text-ink-300 text-lg leading-none shrink-0">&times;</button>
            </div>
        @empty
            <p class="text-xs text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">
                Belum ada yang ditulis. Ketik di atas lalu tekan Enter.
            </p>
        @endforelse
    </div>
</div>
